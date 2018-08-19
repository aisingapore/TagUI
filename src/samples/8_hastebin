https://hastebin.com
// this flow is used by TagUI upload option to upload flow result to hastebin.com

// upload automation flow and log to hastebin.com
// url may expire ~30 days after last view date
// to use - tagui 8_hastebin flow_filename

// join input filename with spaces (input parameters p1, p2 etc..)
// use js step to prevent natural language processing of if condition
// otherwise statement should be entered on the next line after condition 
js if (p2) p1 = p1 + ' ' + p2; if (p3) p1 = p1 + ' ' + p3; if (p4) p1 = p1 + ' ' + p4;
js if (p5) p1 = p1 + ' ' + p5; if (p6) p1 = p1 + ' ' + p6; // done for readability

// combine flow and log from input filename
var fs = require('fs');
flow_data = fs.read(abs_file(p1));
log_data = fs.read(abs_file(p1) + '.log');
combined_data = flow_data + '\n----------------------------\n' + log_data;

// clean up characters that may corrupt text entry
combined_data = combined_data.replace(/[\x00-\x09]/g,'');
combined_data = combined_data.replace(/[\x0E-\x1F]/g,'');
combined_data = combined_data.replace(/[\x80-\xFE]/g,'');

// type data + save data + retrieve raw text url
type //textarea as `combined_data`
click save function button-picture enabled
click raw function button-picture enabled
echo 'UPLOADED URL - ' this.getCurrentUrl() + '\n'
