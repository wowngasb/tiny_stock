# coding: utf-8
import os
import sys
from flask import Flask
from flask_sqlalchemy import SQLAlchemy


app = Flask(__name__)
app.config.setdefault('SQLALCHEMY_TRACK_MODIFICATIONS', True)
db = SQLAlchemy(app)
# 本文件必须命名为 app.py 来 hook models 模块中的app 而不需要数据库配置及连接

sys.path.append(os.path.join(os.path.dirname(os.getcwd()), 'app'))
import models
schema = models.schema
tables = models.tables


