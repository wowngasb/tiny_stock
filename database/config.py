# coding: utf-8
import os
import json

basedir = os.path.abspath(os.path.dirname(__file__))

def _read_file(file_name):
    with open(file_name, 'r') as rf:
        return rf.read()

def _write_file(file_name, str_text):
    with open(file_name, 'w') as wf:
        wf.write(str_text)

def _load_config():
    config_dir = os.path.join(os.path.dirname(basedir), 'config')
    dump = os.path.join(config_dir, 'dumpjson.php')
    config = os.path.join(config_dir, 'app-config.ignore.php')
    config_json = os.path.join(config_dir, 'app-config.ignore.json')
    cfg = {}
    if os.path.isfile(config):
        output = os.popen('php {dump} {config}'.format(dump=dump, config=config))
        cfg = json.load(output) if output else {}
    else:
        output = _read_file(config_json)
        output = output.strip() if output else ''
        cfg = json.loads(output) if output else {}
    _write_file(config_json, json.dumps(cfg, indent=4))
    return cfg

PHP_CONFIG = _load_config()
_ENV_DB = PHP_CONFIG.get('ENV_DB', {})

db_config = {
    'driver': _ENV_DB.get('driver', 'mysql'),
    'host': _ENV_DB.get('host', '127.0.0.1'),
    'port': _ENV_DB.get('port', 3306),
    'database': _ENV_DB.get('database', 'test') + '_tpl',
    'username': _ENV_DB.get('username', 'root'),
    'password': _ENV_DB.get('password', 'root'),
    'charset': _ENV_DB.get('charset', 'utf8'),
}


def dump_db(outfile, tables = '*', nodata = True):
    cmd = "mysqldump --host=%s --port=%s --user=%s --password=\"%s\" --databases %s" % (db_config['host'], db_config['port'], db_config['username'], db_config['password'], db_config['database'])
    if tables == '*':
        pass
    else:
        cmd += "--tables %s" % (tables, )

    if nodata :
        cmd += ' --no-data'

    if outfile :
       cmd += ' > ' + outfile

    ret = os.system(cmd)
    print "RUM cmd : %s" % (cmd, )
    print "EXIT code : %s" % (ret, )


SQLALCHEMY_TRACK_MODIFICATIONS = True

SQLALCHEMY_DATABASE_URI = "{driver}://{username}:{password}@{host}:{port}/{database}?charset={charset}".format(**db_config)

SQLALCHEMY_MIGRATE_REPO = os.path.join(basedir, 'db_repository')