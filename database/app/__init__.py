# coding: utf-8
import os
import hashlib
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
import graphene
import graphql
from flask_graphql import GraphQLView

app = Flask(__name__)
app.config.from_object('config')
db = SQLAlchemy(app)
from app import models

def md5key(pwd):
    def _md5_str(in_str):
        m2 = hashlib.md5()
        m2.update(in_str)
        return m2.hexdigest().lower()

    SECRET_KEY = app.config.get('PHP_CONFIG', {}).get('CRYPT_KEY', '')
    tmp = _md5_str(pwd)
    tmp = _md5_str( SECRET_KEY + tmp )
    return _md5_str( tmp + SECRET_KEY )

@app.route('/')
@app.route('/index')
def index():
    return GRAPHIQL_HTML

@app.route('/test')
def test():
    return 'test'

app.add_url_rule('/graphql', view_func=GraphQLView.as_view('graphql', schema=models.schema, graphiql=True))

if __name__ == '__main__':
    _schema = models.schema
    test_str = '''
query IntrospectionQuery { __schema { queryType { name } mutationType { name } subscriptionType { name } types { ...FullType } directives { name description locations args { ...InputValue } } } } fragment FullType on __Type { kind name description fields(includeDeprecated: true) { name description args { ...InputValue } type { ...TypeRef } isDeprecated deprecationReason } inputFields { ...InputValue } interfaces { ...TypeRef } enumValues(includeDeprecated: true) { name description isDeprecated deprecationReason } possibleTypes { ...TypeRef } } fragment InputValue on __InputValue { name description type { ...TypeRef } defaultValue } fragment TypeRef on __Type { kind name ofType { kind name ofType { kind name ofType { kind name } } } }
    '''
    test_str = '''
{
  curUser{
    uid
  }
}
'''

    test = _schema.execute(test_str)
    import json
    print 'data:', json.dumps(test.data, indent=2)
    print 'errors:', test.errors


GRAPHIQL_HTML = '''
<!DOCTYPE html>
<html>
<head>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      width: 100%;
    }
  </style>
  <link href="http://cdn.jsdelivr.net/graphiql/0.7.1/graphiql.css" rel="stylesheet" />
  <script src="http://cdn.jsdelivr.net/fetch/0.9.0/fetch.min.js"></script>
  <script src="http://cdn.jsdelivr.net/react/15.0.0/react.min.js"></script>
  <script src="http://cdn.jsdelivr.net/react/15.0.0/react-dom.min.js"></script>
  <script src="http://cdn.jsdelivr.net/graphiql/0.7.1/graphiql.min.js"></script>
</head>
<body>
  <script>
    // Collect the URL parameters
    var parameters = {};
    window.location.search.substr(1).split('&').forEach(function (entry) {
      var eq = entry.indexOf('=');
      if (eq >= 0) {
        parameters[decodeURIComponent(entry.slice(0, eq))] =
          decodeURIComponent(entry.slice(eq + 1));
      }
    });

    // Produce a Location query string from a parameter object.
    function locationQuery(params) {
      return location.protocol + '//' + location.host + '/graphql?' + Object.keys(params).map(function (key) {
        return encodeURIComponent(key) + '=' +
          encodeURIComponent(params[key]);
      }).join('&');
    }

    // Derive a fetch URL from the current URL, sans the GraphQL parameters.
    var graphqlParamNames = {
      query: true,
      variables: true,
      operationName: true
    };

    var otherParams = {};
    for (var k in parameters) {
      if (parameters.hasOwnProperty(k) && graphqlParamNames[k] !== true) {
        otherParams[k] = parameters[k];
      }
    }
    var fetchURL = locationQuery(otherParams);

    // Defines a GraphQL fetcher using the fetch API.
    function graphQLFetcher(graphQLParams) {
      return fetch(fetchURL, {
        method: 'post',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(graphQLParams),
        credentials: 'include',
      }).then(function (response) {
        return response.text();
      }).then(function (responseBody) {
        try {
          return JSON.parse(responseBody);
        } catch (error) {
          return responseBody;
        }
      });
    }

    // When the query and variables string is edited, update the URL bar so
    // that it can be easily shared.
    function onEditQuery(newQuery) {
      parameters.query = newQuery;
      updateURL();
    }

    function onEditVariables(newVariables) {
      parameters.variables = newVariables;
      updateURL();
    }

    function onEditOperationName(newOperationName) {
      parameters.operationName = newOperationName;
      updateURL();
    }

    function updateURL() {
      history.replaceState(null, null, locationQuery(parameters));
    }

    // Render <GraphiQL /> into the body.
    ReactDOM.render(
      React.createElement(GraphiQL, {
        fetcher: graphQLFetcher,
        onEditQuery: onEditQuery,
        onEditVariables: onEditVariables,
        onEditOperationName: onEditOperationName,
        query: "",
        response: "",
        variables: null,
        operationName: null,
      }),
      document.body
    );
  </script>
</body>
</html>
'''