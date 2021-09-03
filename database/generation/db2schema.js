var fs = require("fs");
var request = require('request');
var graphql = require("graphql");

/*
import { writeFileSync } from "fs";
import {
  GraphQLSchema,
  buildClientSchema,
  getIntrospectionQuery,
  ExecutionResult,
  IntrospectionQuery,
  introspectionFromSchema,
  printSchema,
  parse
} from "graphql";
*/

function httpRequest(url, data, callback){
    request({
        url: url,
        method: "POST",
        json: true,
        headers: {
            "content-type": "application/json",
        },
        body: data
    }, function(error, response, body) {
        if (!error && response.statusCode == 200) {
            callback && callback(body);
        } else {
            console.log('httpRequest error', error, response)
        }
    });
}

var args = process.argv.splice(2);

var endpoint = args[0] || 'http://127.0.0.1:8086/graphql';
var output = args[1] || 'schema';


httpRequest(endpoint, {
  query: graphql.getIntrospectionQuery()
}, function(data) {
  var schema = graphql.buildClientSchema(data.data);
  fs.writeFileSync(output + '.json', JSON.stringify(graphql.introspectionFromSchema(schema), null, 2));
  fs.writeFileSync(output + '.graphql', graphql.printSchema(schema));
});

