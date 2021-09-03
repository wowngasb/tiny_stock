# coding: utf-8
import os
from config import SQLALCHEMY_DATABASE_URI
from config import dump_db

from sqlalchemy.engine import create_engine
from app import models
from sqlalchemy.inspection import inspect as sqlalchemyinspect
from sqlalchemy import schema
import json

from pykl.tiny.dumptable import get_col_doc

def addslashes(s, l = ["\\", '"', "'", "\0"]):
    for i in l:
        s = s.replace(i, '\\' + i)
    return s

def fstr(QUOTES, ESCAPE = '\\'): ##i[si] is " or ', return index of next i[si] without \ before it
    QUOTES = QUOTES.strip()
    def _fstr(index, s, sl):
        _index = index
        while _index<sl and s[_index] != QUOTES:
            _index += 1

        index = _index
        _index += 1
        while _index<sl and s[_index] != QUOTES:
            _index += 2 if s[_index]==ESCAPE else 1

        return _index+1, s[index+1:_index]

    return _fstr

def fixunicode(ustr):
    _, default_1 = fstr('"')(0, ustr, len(ustr))
    _, default_2 = fstr("'")(0, ustr, len(ustr))
    default = default_1 if default_1 else default_2
    if r'\u' in default:
        try:
            _default = json.loads('"{0}"'.format(default)).encode('gbk').decode('utf-8')
        except UnicodeDecodeError as ex:
            _default = json.loads('"{0}"'.format(default)).encode('utf-8').decode('utf-8')

        ustr = ustr.replace(default, _default).encode('utf-8')
        pass
    return ustr

def update_comment(doc_map, engine, tablename, tabledoc, col_map):
    dbname = engine.url.database
    tabledoc = tabledoc.strip().encode('utf-8') if tabledoc else ''
    tbl_str = ''' ALTER TABLE `{dbname}`.`{tablename}` COMMENT "{tabledoc}" '''.format(
        dbname = dbname,
        tablename = tablename,
        tabledoc = addslashes(tabledoc),
    )
    if doc_map[tablename]['Comment'].encode('utf-8') != tabledoc and tabledoc:
        engine.execute(tbl_str)
        print 'execute table', tbl_str.decode('utf-8')
    else:
        print 'table Comment pass', tablename

    eq_map = {
        u'自增主键': lambda column: column.get('Extra', '') == 'auto_increment',
        u'创建时间': lambda column: column.get('Field', '') == 'created_at',
        u'更新时间': lambda column: column.get('Field', '') == 'updated_at',
    }
    columns = doc_map[tablename]['Columns']
    for col, item in col_map.items():
        if col not in columns:
            continue

        last_comment = columns[col].get('Comment', '')
        doc = item.get('doc', '').strip().encode('utf-8') if item.get('doc', '') else ''
        _type = item.get('type', '').strip().encode('utf-8') if item.get('type', '') else ''
        if columns[col].get('Extra', '') == 'auto_increment':
            _type += ' AUTO_INCREMENT'
        _type = fixunicode(_type)
        if not _type:
            continue
        sql_str = ''' ALTER TABLE `{dbname}`.`{tablename}` MODIFY COLUMN `{col}`  {_type} COMMENT "{doc}" '''.format(
            dbname = dbname,
            tablename = tablename,
            col = col,
            doc = addslashes(doc),
            _type = _type,
        )
        if doc:
            is_same = last_comment.encode('utf-8') == doc
            is_eq = eq_map.get(last_comment, lambda s: True)(columns[col])
            if not (is_same and is_eq):
                print 'execute col', sql_str.decode('utf-8')
                engine.execute(sql_str.replace("%", "%%"))
                continue

        print 'table Columns Comment pass', tablename, col

def main():
    _type = lambda c: str( schema.CreateColumn(c) ).split(' ', 1)[-1].strip()
    _item = lambda c : {'doc':getattr(c, 'doc', None), 'type': _type(c)}

    engine, tables = create_engine(SQLALCHEMY_DATABASE_URI), models.tables
    doc_map = get_col_doc(engine)
    for _table in tables:
        table = sqlalchemyinspect(_table)
        update_comment(doc_map, engine, _table.__tablename__, _table.__doc__, {k: _item(v) for k, v in table.columns.items()})

    dump_db('tpl.sql')

if __name__ == '__main__':
    main()