casper.then(function() {if (excel_files.length != 0) excel_close();});

casper.then(function() {techo('\n' + this.getCurrentUrl() + ' - ' + this.getTitle());
techo('FINISH - automation finished - ' + ((Date.now()-automation_start_time)/1000).toFixed(1) + 's\n');});

casper.run();
