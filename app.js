var express = require('express');
var app = express();
// var router  = express.Router();
// var morgan = require('morgan');

// app.use(morgan('dev'));

app.use(express.static(__dirname+'/ng-upload'));



app.listen(process.env.PORT || 4343);

