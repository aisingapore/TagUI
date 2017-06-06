# SIKULI INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG #

# timeout in seconds for finding a web element
setAutoWaitTimeout(10)

# delay in seconds between scanning for inputs
scan_period = 1

# counter to track current tagui sikuli step
tagui_count = '0'

# prevent premature exit on unhandled exception
setThrowException(False)

# function for tap / click step
def tap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - click ' + params
	if exists(params):
		return click(params)
	else:
		return 0

# function for hover / move step
def hover_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - hover ' + params
	if exists(params):
		return hover(params)
	else:
		return 0

# function for type / enter step
def type_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	print '[tagui] ACTION - type ' + param1 + ' as ' + param2
	if exists(param1):
		return type(param1,param2) 
	else:
		return 0

# function for select / choose step
def select_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	print '[tagui] ACTION - click ' + param1
	if exists(param1):
		if click(param1) == 0:
			return 0
		else:
			print '[tagui] ACTION - click ' + param2
			if exists(param2):
				return click(param2)
			else:
				return 0
	else:
		return 0

# function to interpret input intent
def get_intent ( raw_intent ):
	if raw_intent[:4].lower() == 'tap ' or raw_intent[:6].lower() == 'click ':
		return 'tap'
	if raw_intent[:6].lower() == 'hover ' or raw_intent[:5].lower() == 'move ': 
		return 'hover'
	if raw_intent[:5].lower() == 'type ' or raw_intent[:6].lower() == 'enter ': 
		return 'type'
	if raw_intent[:7].lower() == 'select ' or raw_intent[:7].lower() == 'choose ':
		return 'select'
	return 'error'

# function to parse and act on intent
def parse_intent ( script_line ):
	intent_type = get_intent(script_line)
	if intent_type == 'tap':
		return tap_intent(script_line)
	elif intent_type == 'hover':
		return hover_intent(script_line)
	elif intent_type == 'type':
		return type_intent(script_line)
	elif intent_type == 'select':
		return select_intent(script_line)
	else:
		return 0 

# write to interface out-file to signal ready for inputs
tagui_output = open('tagui.sikuli/tagui_sikuli.out','w')
tagui_output.write('[0] START')
tagui_output.close()

# initialise interface in-file before starting main loop
tagui_input = open('tagui.sikuli/tagui_sikuli.in','w')
tagui_input.write('')
tagui_input.close()

# main loop to scan inputs from automation flow
print '[tagui] START  - listening for inputs'; print
while True:
	# scan input from run-time interface in-file
	try:
		tagui_input = open('tagui.sikuli/tagui_sikuli.in','r')
	except IOError, OSError:
		print '[tagui] ERROR  - cannot open tagui.sikuli/tagui_sikuli.in'; print
		break
	tagui_intent = tagui_input.read().strip()
	tagui_input.close()

	# quit if finish signal received, initialise and repeat loop if blank
	if tagui_intent == 'finish':
		break
	elif not tagui_intent:
		tagui_count = '0'
		tagui_output = open('tagui.sikuli/tagui_sikuli.out','w')
		tagui_output.write('[0] START')
		tagui_output.close()
		wait(scan_period)
		continue

	# get count and repeat loop if same count as last iteration
	temp_count = tagui_intent[1:tagui_intent.find('] ')].strip()
	tagui_intent = tagui_intent[2+tagui_intent.find('] '):].strip()
	if tagui_count == temp_count:
		wait(scan_period)
		continue
	else:
		tagui_count = temp_count

	# otherwise parse and act on input intent
	print '[tagui] INPUT  - ' + '[' + tagui_count + '] ' + tagui_intent 
	intent_result_value = parse_intent(tagui_intent)
	if intent_result_value == 1:
		intent_result_string = 'SUCCESS'
	else:
		intent_result_string = 'ERROR'

	# save intent_result to interface out-file
	print '[tagui] OUTPUT - ' + '[' + tagui_count + '] ' + intent_result_string; print
	tagui_output = open('tagui.sikuli/tagui_sikuli.out','w')
	tagui_output.write('[' + tagui_count + '] ' + intent_result_string)
	tagui_output.close()
	wait(scan_period)

# write to interface out-file to signal finish listening
print '[tagui] FINISH - stopped listening'
tagui_output = open('tagui.sikuli/tagui_sikuli.out','w')
tagui_output.write('[0] FINISH')
tagui_output.close()
