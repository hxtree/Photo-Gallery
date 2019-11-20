'''
Photo Processing Script

REQUIRED:
	sudo apt-get install python-mysqldb
	sudo apt-get install libmagickwand-dev
	sudo apt-get install python-pip
	sudo pip install configparser
	sudo pip install Wand
	sudo pip install tendo
	sudo apt-get install imagemagick

INSTALL:
	sudo crontab -e
	*/5 * * * * python /var/www/photos/process-files.py

REFERENCES:
	http://docs.wand-py.org/en/0.4.4/guide/exif.html
'''
import inspect, os, shutil, datetime, time, string, configparser, fnmatch, os, MySQLdb, sys, ntpath, math, glob
import MySQLdb.cursors as cursors
from wand.image import Image
from wand.display import display
from tendo import singleton
from pprint import pprint

def main():
	reload(sys)
	sys.setdefaultencoding('utf-8')
	#sys.tracebacklimit=0
	# allow only one instance of a script
	me = singleton.SingleInstance()
	# change the relative directory
	os.chdir(os.path.dirname(os.path.realpath(__file__)))
	# get local ini config
	config = configparser.ConfigParser()
	config.read('../resources/config/photo-gallery.conf')
	config.read('../resources/config/default.conf')
	output_dir = config['PHOTO GALLERY']['output_dir']
	input_dir = config['PHOTO GALLERY']['input_dir']
	thumbnail_sizes = config['PHOTO GALLERY']['thumbnail_sizes'].split(',')
	white_list =  tuple(config['PHOTO GALLERY']['white_list'].encode('ascii', 'ignore').split(', '))
	black_list =  tuple(config['PHOTO GALLERY']['black_list'].encode('ascii', 'ignore').split(', '))

	# connect to mysql
	db = MySQLdb.connect(host=config['DATABASE']['host'],user=config['DATABASE']['user'],passwd=config['DATABASE']['password'],db=config['DATABASE']['dbname'], cursorclass=cursors.SSCursor)
	db.set_character_set('utf8')

	skip = 0
	print '<<script start>>'

	# get all files
	for root, dirs, files in os.walk(input_dir):
		# process all files
		for name in files:
			file_path = os.path.join(root, name)
			file_exension = ''
			#check if empty dir and remove
			file_name, file_extension = os.path.splitext(file_path)
			file_name = str(ntpath.basename(file_path))
			#print ' >file_name:' + file_name

			if file_extension is None or not file_extension.lower().endswith(white_list):
				# remove black_list item
				if file_extension.lower().endswith(black_list) or ((file_extension is None or file_extension == '') and file_name.lower().endswith(black_list)):
					try:
						os.remove(file_path)
						print 'delete:' +file_path
					except:
						print 'delete failed:' + file_path
				else:
					skip += 1
					#print 'skip:' + file_path
				continue
			elif file_name.lower().startswith('._') or file_extension.lower().startswith('._'):
				# delete white_list items with '._', e.g. '._fall view LOCATION.jpg'
				try:
					os.remove(file_path)
					print 'delete:' +file_path
				except:
					print 'delete failed:' + file_path
			elif './' in file_path or './' in file_name:
				skip += 1
				#print 'skip:' + file_path
			else:
				print 'process:' + file_path

				file_relative_path =  file_path.replace(input_dir, '')
				#print ' >file_relative_path:' + file_relative_path

				file_dir = file_relative_path.replace(file_name,'')
				#print ' >file_dir:' + file_dir

				file_basename = file_name.replace(file_extension,'')
				#print ' >file_basename:' + file_basename

				# second zero'd out to avoid leap seconds issues
				file_last_modified = time.strftime('%Y-%m-%d %H:%M:00', time.gmtime(os.path.getmtime(file_path)))
				#print ' >file_last_modified:' + file_last_modified

				try:
					# insert file record into file table
					cursor = db.cursor()
					cursor.execute('INSERT INTO `file` (`format`,`last_modified`,`processed`) VALUES (%s,%s,NOW())',(file_extension.lower(),file_last_modified))
					file_id = cursor.lastrowid
					#print ' >file_id:' + str(file_id)

					if not os.path.exists(output_dir + '/' + str(file_id)):
						os.makedirs(output_dir + '/' + str(file_id))

					# rename file to orignal and place in file_id directory
					file_new_path = output_dir + '/' + str(file_id) + '/original' + file_extension.lower()
					shutil.move(file_path, file_new_path)
					db.commit()
					cursor.close()
				except:
					print 'Unable to move: ' + file_path + ' to new path'
					pass
					continue

				cursor = db.cursor()
				# insert record of file version
				cursor.execute('INSERT INTO `file_version` (`file_id`,`filename`,`processed`) VALUES (%s,%s,NOW());',(file_id,'original' + file_extension.lower()))
				db.commit()
				cursor.close()

				# insert tags based on file path
				#print ' >file_tag(s):'
				tags = file_dir.split('/')
				tags.append(file_basename)
				cursor = db.cursor()
				for tag in tags:
					if tag is None or tag == '':
						continue
					cursor.execute('INSERT INTO `file_tag` (`file_id`,`tag`) VALUES (%s,%s)',(file_id,tag.strip()))
					db.commit()
					#print '  >'+tag
				cursor.close()
				cursor = db.cursor()

				# add path hierarchy to database using adjacency list model
				#print ' >file_path(s):'
				paths =  file_relative_path.split('/')
				parent_id = None
				current_path = input_dir
				for path in paths:
					if path is None or path == '':
						#print '  >path none or blank'
						continue
					current_path += '/'+ path
					refined_path = path.strip()
					#print '  >'+refined_path
					# detect if entry exists
					cursor = db.cursor()
					if parent_id is None:
						cursor.execute('SELECT `directory_id` FROM `file_adjacency_list` WHERE `name` = %s AND `file_id` IS NULL AND `parent_id` IS NULL LIMIT 1',[refined_path])
					else:
						cursor.execute('SELECT `directory_id` FROM `file_adjacency_list` WHERE `name` = %s AND `file_id` IS NULL AND `parent_id` = %s LIMIT 1',[refined_path, str(parent_id)])
					row = cursor.fetchone()
					if row:
						parent_id = row[0]
					else:
						# insert file or folder that doesn't exist
						cursor = db.cursor()
						# does not exits
						if os.path.isdir(current_path):
							cursor.execute('INSERT INTO `file_adjacency_list` (`name`,`parent_id`,`file_id`) VALUES (%s,%s,NULL);',(refined_path, parent_id))
							db.commit()
							parent_id = cursor.lastrowid
							#print '   >Add:' + file_name + ' parent_id: ' + str(parent_id)
						else:
							cursor.execute('INSERT INTO `file_adjacency_list` (`name`,`parent_id`,`file_id`) VALUES (%s,%s,%s);',(refined_path, parent_id,file_id))
							db.commit()
							#print '   >Add:' + file_name + ' parent_id: ' + str(parent_id) + ' file_id: ' + str(file_id)
						cursor.close()
					#else:
						#print '   >path exists in db'

				# try to update last modified based on data contained in
				try:
					file_original_path = output_dir + '/' + str(file_id) + '/original' + file_extension.lower()
					if not os.path.isfile(file_original_path):
						print 'missing file: '+ file_original_path
						continue
					try:
						image = Image(filename=file_original_path)
						exif = {}
						for k, v in image.metadata.items():
							if k is None or v is None:
								continue
							exif[k] = v
						if 'date:create' in exif:
							ts = time.strptime(exif['date:create'][:19], "%Y-%m-%dT%H:%M:%S")
							cursor = db.cursor()
							cursor.execute('UPDATE `file` SET `last_modified` = %s WHERE `file_id` = %s LIMIT 1',(time.strftime('%Y-%m-%d %H:%M:00', ts),file_id))
							db.commit()
							cursor.close()
						else:
							raise ValueError('date:create did not exist')
						image.close()
					except:
						pass
				except ValueError as e:
					print ' could not obtain exif date from file ' + e
					pass
		'''
		for name in dirs:
			# remove empty directories one folder per process
			print 'checking dir: ' + name
			file_dir = os.path.join(root, name)
			if os.path.isdir(file_dir):
				if not os.listdir(file_dir):
					try:
						os.rmdir(file_dir)
						print 'removing dir: ' + name
					except:
						print 'remove dir failed' + name
						pass
				continue
		'''
	# generate thumbnails for files without thumbnails
	for root, dirs, files in os.walk(output_dir):
		for file_id in dirs:
			file_dir = os.path.join(root, file_id)
			# check if 300 thumbnail exists
			if not os.path.isfile(file_dir + '/300.jpg'):
				try:
					# find complete name of original file
					file_original_path = glob.glob(os.path.join(file_dir, 'original.*'))[0]
					# load original image
					try:
						image = Image(filename=file_original_path)
						old_width = int(image.width)
						old_height = int(image.height)
						image.close()

						# create thumbnails
						for size in thumbnail_sizes:
							thumbnail = str(size).strip() + '.jpg'
							size = int(size)
							file_thumb_path = output_dir + '/' + file_id + '/' + thumbnail

							if old_width<size and old_height<size:
								#print ' Skip thumbnail size ' + str(size);
								continue
							# determine scale for height and width
							if old_width>old_height:
								new_width = size
								new_height = old_height * size / old_width
							else:
								new_height = size
								new_width = old_width * size / old_height
							# check for ratio error
							if new_height <1 or new_width <1:
								continue
							# mogrify provided better  quality than image.resize
							if os.system('mogrify -auto-orient -layers flatten -format jpg -thumbnail '+str(new_width)+'x'+str(new_height)+' -write '+ file_thumb_path + ' ' + file_original_path) == 0:
								cursor = db.cursor()
								cursor.execute('INSERT INTO `file_version` (`file_id`,`filename`,`processed`) VALUES (%s,%s,NOW());',(file_id,thumbnail));
								db.commit()
								print 'created: '+thumbnail+' thumbnail for file#' + file_id
							else:
								print 'failed: '+thumbnail+' thumbnail for file#' + file_id
					except:
						print 'Count not load original: ' + file_original_path
						pass
				except:
					print 'original file missing: ' + file_dir
					pass

	# REBUILD SEARCH TABLE (i.e. file_search)
	print 'rebuilding _file_search table'

	# drop new table in case it exists due to some error
	cursor = db.cursor()
	cursor.execute('DROP TABLE IF EXISTS `_file_search_new`;')
	db.commit()

	# create table
	cursor = db.cursor()
	cursor.execute('CREATE TABLE _file_search_new (`file_id` INT(11) NOT NULL AUTO_INCREMENT, `filename` VARCHAR (255) COLLATE \'utf8_unicode_ci\' DEFAULT NULL , `tags` VARCHAR(500) COLLATE \'utf8_unicode_ci\' DEFAULT NULL , `last_modified` DATETIME, `processed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(`file_id`));')
	db.commit()

	# insert file id
	cursor = db.cursor()
	cursor.execute('INSERT INTO _file_search_new (`file_id`,`last_modified`) SELECT `file_id`, `last_modified` FROM `file`;')
	db.commit()
	print '> adding file metadata'

	# update tags
	cursor = db.cursor()
	cursor.execute('UPDATE `_file_search_new` INNER JOIN (SELECT `file_id`, GROUP_CONCAT(`tag` SEPARATOR \',\') AS `tags` FROM `file_tag` GROUP BY `file_id`) AS `tags_table` ON `_file_search_new`.`file_id` = `tags_table`.`file_id` SET `_file_search_new`.`tags` = `tags_table`.`tags`;')
	db.commit()
	print '> adding tags'

	# update filename
	cursor = db.cursor()
	cursor.execute('UPDATE `_file_search_new` INNER JOIN (SELECT `file_id`,`name` FROM `file_adjacency_list` WHERE `file_id` IS NOT NULL) AS `name_table` ON `_file_search_new`.`file_id` = `name_table`.`file_id` SET `_file_search_new`.`filename` = `name_table`.`name`;')
	db.commit()
	print '> adding filenames'

	# rename tables at once to help prevent any seen downtime where table does not exist
	cursor = db.cursor()
	cursor.execute('RENAME TABLE `_file_search` TO `_file_search_old`, `_file_search_new` TO `_file_search`;')
	db.commit()

	# drop old table
	cursor = db.cursor()
	cursor.execute('DROP TABLE IF EXISTS `_file_search_old`;')
	db.commit()
	print '> switched out tables'


	db.close()
	print '<<script end>>'
if __name__ == "__main__":
	main()
