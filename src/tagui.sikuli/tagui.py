# SIKULI INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG #

# timeout in seconds for finding a web element
setAutoWaitTimeout(10)

# delay in seconds between scanning for inputs
scan_period = 1

# function for tap / click step
def tap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	return click(params)

# function for hover / move step
def hover_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	return hover(params)

# function for type / enter step
def type_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	return type(param1,param2) 

# function for select / choose step
def select_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	if click(param1) == 0:
		return 0
	else:
		return click(param2)

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

# main loop to scan inputs from automation flow
while True:
	# scan input from run-time interface in-file
	tagui_input = open('tagui.sikuli/tagui_sikuli.in','r')
	tagui_intent = tagui_input.read().strip()
	tagui_input.close()

	# quit if finished, repeat loop if blank
	if tagui_intent == 'finished':
		break
	elif not tagui_intent:
		wait(scan_period)
		continue

	# otherwise parse and act on input intent
	intent_result = parse_intent(tagui_intent)

	# save intent_result to interface out-file
	tagui_output = open('tagui.sikuli/tagui_sikuli.out','w')
	tagui_output.write(str(intent_result))
	tagui_output.close()
	wait(scan_period)
