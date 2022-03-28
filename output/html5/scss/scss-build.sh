#!/bin/bash

echo use directives:bootstrap:forcemake to make scss && exit
cd scss && cp bootswatch-package.json package.json && npm install && npm run css
