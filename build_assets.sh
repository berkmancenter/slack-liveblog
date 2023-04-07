#!/bin/bash

VENDOR_CSS=dist/vendor.css
VENDOR_JS=dist/vendor.js

echo '' > $VENDOR_CSS
echo '' > $VENDOR_JS

cat node_modules/awesome-notifications/dist/style.css >> $VENDOR_CSS
cat node_modules/awesome-notifications/dist/modern.var.js >> $VENDOR_JS
