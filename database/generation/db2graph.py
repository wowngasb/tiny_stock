#-*- coding: utf-8 -*-
import os

from pykl.tiny.grapheneinfo import BuildType

from app import schema, tables
from pykl.tiny.codegen import BuildPHP, BuildGO, BuildJAVA, BuildAliApiPHP, BuildFlutter

from pykl.tiny.grapheneinfo.utils import (
    BitMask,
    HiddenField,
    InitializeField,
    EditableField,
    SortableField,
    CustomField,
    upper_tuple,
    mask_field,
    mask_keys,
)

from pykl.tiny.codegen.utils import (
    Options,
    camel_to_underline,
    underline_to_camel,
    name_from_repr,
    merge_default,
    save_file,
    path_join,
    render_template_file,
    _is_base,
    _is_enum,
    _is_union,
    _is_sqlalchemy,
    _is_abstract_sqlalchemy,
    _is_simple,
    _php_namespace,
)

from sqlalchemy.inspection import inspect as sqlalchemyinspect

import json

def main():
    _d = os.path.dirname

    _output = lambda *tag: os.path.join(os.getcwd(), 'output', *tag)

    _app_output = lambda *s: os.path.join( _d(_d(os.getcwd())), 'app', *s)

    out_json = os.path.join(os.getcwd(), 'model.json')
    _dump_model_json(schema, tables, out_json)

    BuildPHP(schema=schema, tables=tables, output=_output('phpsrc'), graphql=dict(output=_app_output('api')), model_=dict(output=_app_output())).build()

    BuildGO(schema=schema, tables=tables, output=_output('gosrc')).build()
    BuildJAVA(schema=schema, tables=tables, output=_output('javasrc')).build()

    print "======== END ========="


def _build_column_info(column):
    info = getattr(column, 'info', None)
    _h = lambda info, bit_mask: info.has(bit_mask) if isinstance(info, BitMask) else False
    return {
        'HiddenField': _h(info, HiddenField),
        'InitializeField': _h(info, InitializeField),
        'EditableField': _h(info, EditableField),
        'SortableField': _h(info, SortableField),
    }

def _build_column_type(column):
    atype = str(column.type)
    if atype.startswith('VARCHAR'):
        atype = 'VARCHAR'
    if atype.startswith('CHAR'):
        atype = 'CHAR'

    dmap = {
        'INTEGER': "int",
        'MEDIUMINT':"int",
        'BIGINT':"int",
        'SMALLINT': "int",
        'CHAR': "String",
        'VARCHAR': "String",
        'TIMESTAMP': "String",
        'DATETIME': "String",
        'BOOL': 'bool',
        'DOUBLE': 'float',
        'FLOAT': 'float',
    }
    return dmap.get(atype, "String")

def _build_model_columns(table):
    columns = []
    for name, column in table.columns.items():
        tmp = {
            'type': _build_column_type(column),
            'name': name,
            'key': column.key,
            'doc': column.doc if column.doc else '',
            'index': column.index,
            'nullable': column.nullable,
            'unique': column.unique,
            'default': column.default,
            'server_default': column.server_default.arg.text if column.server_default else None,
            'primary_key': column.primary_key,
            'foreign_keys': list(column.foreign_keys),
            'info': _build_column_info(column)
        }
        columns.append(tmp)

    return columns

def _dump_model_json(schema, tables, out_file):
    classname = lambda t: name_from_repr(t)

    obj = {}
    for _table in tables:
        table = sqlalchemyinspect(_table)

        table_name = table.class_.__tablename__
        obj[table_name] = {
            'table_name': table_name,
            'class_name': name_from_repr(table),
            'class_doc': table.class_.__doc__,
            'primary_key': table.primary_key[0].name,
            'columns': _build_model_columns(table)
        }

    with open(out_file, 'w') as wf:
        return json.dump(obj, wf, indent=2)

if __name__ == '__main__':
    main()

