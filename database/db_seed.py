# coding: utf-8
import app
from app import db, models, md5key
from app.models import *
from sqlalchemy.sql.expression import func
import random
import datetime, json
import time

def md5Upper(s):
    s = md5key(s)
    s = s[:8]
    return s.upper()

def pri_filter(dao, pri, item_list, churk=200):
    id_set = list(set([item[pri] for item in item_list]))
    tmp = []
    print '\npri_filter %r all:%s' % (dao, len(item_list)),
    while id_set:
        l_tmp = id_set[:churk]
        s_tmp = dao.query.filter( getattr(dao, pri).in_(l_tmp) ).all()
        tmp.extend(s_tmp)
        id_set = id_set[churk:]
        print '.',

    print '\n'
    has_set = set([getattr(i, pri, None) for i in tmp])
    return [t for t in item_list if t[pri] not in has_set]

def date(format_='%Y-%m-%d %H:%M:%S', time_=None):
    timestamp = time.time() if time_ is None else int(time_)
    timestruct = time.localtime(timestamp)
    return time.strftime(format_, timestruct)
