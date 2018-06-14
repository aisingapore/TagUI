casper.then(function() {techo('\n' + this.getCurrentUrl() + ' - ' + this.getTitle());
techo('FINISH - automation finished - ' + ((Date.now()-automation_start_time)/1000).toFixed(1) + 's\n');});

casper.run();
