'''
Fix File Last Modified

REQUIRED:
	sudo apt-get install python-mysqldb
	sudo apt-get install libmagickwand-dev
	sudo apt-get install python-pip
	sudo pip install configparser
	sudo pip install tendo
'''
import inspect, os, shutil, datetime, time, string, configparser, fnmatch, os, MySQLdb, sys, ntpath, math, glob
import MySQLdb.cursors as cursors
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

	# connect to mysql
	db = MySQLdb.connect(host=config['DATABASE']['host'],user=config['DATABASE']['user'],passwd=config['DATABASE']['password'],db=config['DATABASE']['dbname'], cursorclass=cursors.SSCursor)
	db.set_character_set('utf8')

	print '<<script start>>'

	# generate thumbnails for files without thumbnails
	for root, dirs, files in os.walk(output_dir):
		for file_id in dirs:
			file_dir = os.path.join(root, file_id)
			# find complete name of original file
			file_original_path = glob.glob(os.path.join(file_dir, 'original.*'))[0]
			date = os.path.getmtime(file_original_path)
			try:
				cursor = db.cursor()
				cursor.execute('UPDATE `file` SET `last_modified` = %s WHERE `file_id` = %s LIMIT 1',(datetime.datetime.fromtimestamp(date).strftime('%Y-%m-%d %H:%M:%S'),file_id))
				db.commit()
				print 'File#' + file_id + file_original_path + ' date updated to ' + datetime.datetime.fromtimestamp(date).strftime('%Y-%m-%d %H:%M:%S')
			except:
				print 'Failed to update File#' + file_id + file_original_path + ' date to ' + datetime.datetime.fromtimestamp(date).strftime('%Y-%m-%d %H:%M:%S')
	db.close()
	print '<<script end>>'
if __name__ == "__main__":
	main()
