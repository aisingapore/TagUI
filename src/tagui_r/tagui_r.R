# R INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG #

# delay in seconds between scanning for inputs
scan_period = 0.1

# counter to track current tagui r step
tagui_count = '0'

# initialise and set integration interface file
cat('',file='tagui_r/tagui_r.txt',append=FALSE)
sink('tagui_r/tagui_r.txt',split=TRUE,append=FALSE)

# write to interface out-file to signal ready for inputs
cat('[0] START',file='tagui_r/tagui_r.out',append=FALSE)

# initialise interface in-file before starting main loop
cat('',file='tagui_r/tagui_r.in',append=FALSE)

r_intent = function(raw_intent) {
	params = trimws(substr(paste(raw_intent,' ',sep=''),1+regexpr(' ',paste(raw_intent,' ',sep='')),nchar(raw_intent)))
	message(paste('[tagui] ACTION - ',params,sep=''))
	cat('',file='tagui_r/tagui_r.txt',append=FALSE)
	eval(parse(text = params),envir=.GlobalEnv)
	eval_output = trimws(readLines('tagui_r/tagui_r.txt',skipNul=TRUE,warn=FALSE))
	cat(eval_output,file='tagui_r/tagui_r.txt',append=FALSE)
	if (grepl('cat\\(',params)) message()
	return(1)
}

get_intent = function(raw_intent) {
	if (tolower(substr(raw_intent,1,2)) == 'r ') return('r')
	return('error')
}

parse_intent = function(script_line) {
	intent_type = get_intent(script_line)
	if (intent_type == 'r') {
		return(r_intent(script_line))
	} else {
		return(0)
	}
}

# main loop to scan inputs from automation flow
message('[tagui] START  - listening for inputs'); message()
while (TRUE) {
	# scan input from run-time interface in-file
	tryCatch({
		tagui_intent = trimws(readLines('tagui_r/tagui_r.in',skipNul=TRUE,warn=FALSE))
		if (length(tagui_intent) == 0) tagui_intent = ''
	}, error = function(e) {
		message('[tagui] ERROR  - cannot open tagui_r/tagui_r.in'); message(); break
	})

	# quit if finish signal received, initialise and repeat loop if blank
	if (tagui_intent == 'finish') {
		break
	} else if (tagui_intent == '') {
		tagui_count = '0'
		cat('[0] START',file='tagui_r/tagui_r.out',append=FALSE)
		Sys.sleep(scan_period)
		next
	}

	# get count and repeat loop if same count as last iteration
	temp_count = trimws(substr(tagui_intent,2,-1+regexpr('] ',tagui_intent)))
	tagui_intent = trimws(substr(tagui_intent,2+regexpr('] ',tagui_intent),nchar(tagui_intent)))
	if (tagui_count == temp_count) {
		Sys.sleep(scan_period)
		next
	} else {
		tagui_count = temp_count
	}

	# otherwise parse and act on input intent
	message(paste('[tagui] INPUT  - [',tagui_count,'] ',tagui_intent,sep=''))
	intent_result_value = parse_intent(tagui_intent)
	if (intent_result_value == 1) {
		intent_result_string = 'SUCCESS'
	} else {
		intent_result_string = 'ERROR'
	}

	# save intent_result to interface out-file
	message(paste('[tagui] OUTPUT - [',tagui_count,'] ',intent_result_string,sep='')); message()
	cat(paste('[',tagui_count,'] ',intent_result_string,sep=''),file='tagui_r/tagui_r.out',append=FALSE)
	Sys.sleep(scan_period)
}

# write to interface out-file to signal finish listening
message('[tagui] FINISH - stopped listening')
cat('[0] FINISH',file='tagui_r/tagui_r.out',append=FALSE)
