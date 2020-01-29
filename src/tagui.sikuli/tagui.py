# SIKULI INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG #

# timeout in seconds for finding an element
wait_timeout = 10
setAutoWaitTimeout(wait_timeout)

# delay in seconds between scanning for inputs
scan_period = 0.5

# counter to track current tagui sikuli step
tagui_count = '0'

# prevent premature exit on unhandled exception
setThrowException(False)

# enable OCR (optical character recognition)
Settings.OcrTextRead = True
Settings.OcrTextSearch = True

# helper function to detect coordinates locator
def is_coordinates ( input_locator ):
	if input_locator[:1] == '(' and input_locator[-1:] == ')' and input_locator.count(',') in [1,2]:
		return True
	else:
		return False

# helper function to return x coordinate from (x,y)
def x_coordinate ( input_locator ):
	return int(input_locator[1:input_locator.find(',')])

# helper function to return y coordinate from (x,y)
def y_coordinate ( input_locator ):
        return int(input_locator[input_locator.find(',')+1:-1])

# helper function to return Region from (x1,y1)-(x2,y2)
def define_region( input_locator ):
	input_tokens = input_locator.split(',')
	region_x1_coordinate = int(input_tokens[0].replace('(',''))
	region_y1_coordinate = int(input_tokens[1].split('-')[0].replace(')',''))
	region_x2_coordinate = int(input_tokens[1].split('-')[1].replace('(',''))
	region_y2_coordinate = int(input_tokens[2].replace(')',''))
	region_width = abs(region_x2_coordinate - region_x1_coordinate)
	region_height = abs(region_y2_coordinate - region_y1_coordinate)
	region_origin_x = min(region_x1_coordinate,region_x2_coordinate)
	region_origin_y = min(region_y1_coordinate,region_y2_coordinate)
	return Region(region_origin_x,region_origin_y,region_width,region_height)

# helper function to return whether image file exists
def file_exists( full_path_and_filename ):
	import os
	return os.path.isfile(full_path_and_filename)

# helper function to return region containing text to interact by text
def text_locator ( locator ):
	locator = locator[:-4]
	last_back_slash = locator.rfind('/')
	last_forward_slash = locator.rfind('\\')
	last_slash_position = max(last_back_slash, last_forward_slash)
	locator = locator [last_slash_position+1:]
	return findText(locator)

# function to map modifier keys to unicode for use in type()
def modifiers_map ( input_keys ):
	modifier_keys = 0
	if '[shift]' in input_keys or '[SHIFT]' in input_keys: modifier_keys = modifier_keys + KeyModifier.SHIFT
	if '[ctrl]' in input_keys or '[CTRL]' in input_keys: modifier_keys = modifier_keys + KeyModifier.CTRL
	if '[alt]' in input_keys or '[ALT]' in input_keys: modifier_keys = modifier_keys + KeyModifier.ALT
	if '[meta]' in input_keys or '[META]' in input_keys: modifier_keys = modifier_keys + KeyModifier.META
	if '[cmd]' in input_keys or '[CMD]' in input_keys: modifier_keys = modifier_keys + KeyModifier.CMD
	if '[win]' in input_keys or '[WIN]' in input_keys: modifier_keys = modifier_keys + KeyModifier.WIN
	return modifier_keys

# function to map special keys to unicode for use in type()
def keyboard_map ( input_keys ):
	input_keys = input_keys.replace('[clear]','\b').replace('[CLEAR]','\b')
	input_keys = input_keys.replace('[space]',' ').replace('[SPACE]',' ')
	input_keys = input_keys.replace('[enter]','\n').replace('[ENTER]','\n')
	input_keys = input_keys.replace('[backspace]','\b').replace('[BACKSPACE]','\b')
	input_keys = input_keys.replace('[tab]','\t').replace('[TAB]','\t')
	input_keys = input_keys.replace('[esc]',u'\u001b').replace('[ESC]',u'\u001b')
	input_keys = input_keys.replace('[up]',u'\ue000').replace('[UP]',u'\ue000')
	input_keys = input_keys.replace('[right]',u'\ue001').replace('[RIGHT]',u'\ue001')
	input_keys = input_keys.replace('[down]',u'\ue002').replace('[DOWN]',u'\ue002')
	input_keys = input_keys.replace('[left]',u'\ue003').replace('[LEFT]',u'\ue003')
	input_keys = input_keys.replace('[pageup]',u'\ue004').replace('[PAGEUP]',u'\ue004')
	input_keys = input_keys.replace('[pagedown]',u'\ue005').replace('[PAGEDOWN]',u'\ue005')
	input_keys = input_keys.replace('[delete]',u'\ue006').replace('[DELETE]',u'\ue006')
	input_keys = input_keys.replace('[end]',u'\ue007').replace('[END]',u'\ue007')
	input_keys = input_keys.replace('[home]',u'\ue008').replace('[HOME]',u'\ue008')
	input_keys = input_keys.replace('[insert]',u'\ue009').replace('[INSERT]',u'\ue009')
	input_keys = input_keys.replace('[f1]',u'\ue011').replace('[F1]',u'\ue011')
	input_keys = input_keys.replace('[f2]',u'\ue012').replace('[F2]',u'\ue012')
	input_keys = input_keys.replace('[f3]',u'\ue013').replace('[F3]',u'\ue013')
	input_keys = input_keys.replace('[f4]',u'\ue014').replace('[F4]',u'\ue014')
	input_keys = input_keys.replace('[f5]',u'\ue015').replace('[F5]',u'\ue015')
	input_keys = input_keys.replace('[f6]',u'\ue016').replace('[F6]',u'\ue016')
	input_keys = input_keys.replace('[f7]',u'\ue017').replace('[F7]',u'\ue017')
	input_keys = input_keys.replace('[f8]',u'\ue018').replace('[F8]',u'\ue018')
	input_keys = input_keys.replace('[f9]',u'\ue019').replace('[F9]',u'\ue019')
	input_keys = input_keys.replace('[f10]',u'\ue01A').replace('[F10]',u'\ue01A')
	input_keys = input_keys.replace('[f11]',u'\ue01B').replace('[F11]',u'\ue01B')
	input_keys = input_keys.replace('[f12]',u'\ue01C').replace('[F12]',u'\ue01C')
	input_keys = input_keys.replace('[f13]',u'\ue01D').replace('[F13]',u'\ue01D')
	input_keys = input_keys.replace('[f14]',u'\ue01E').replace('[F14]',u'\ue01E')
	input_keys = input_keys.replace('[f15]',u'\ue01F').replace('[F15]',u'\ue01F')
	input_keys = input_keys.replace('[printscreen]',u'\ue024').replace('[PRINTSCREEN]',u'\ue024')
	input_keys = input_keys.replace('[scrolllock]',u'\ue025').replace('[SCROLLLOCK]',u'\ue025')
	input_keys = input_keys.replace('[pause]',u'\ue026').replace('[PAUSE]',u'\ue026')
	input_keys = input_keys.replace('[capslock]',u'\ue027').replace('[CAPSLOCK]',u'\ue027')
	input_keys = input_keys.replace('[numlock]',u'\ue03B').replace('[NUMLOCK]',u'\ue03B')

	# if modifier key is the only input, treat as a keystroke instead of a modifier
	if input_keys == '[shift]' or input_keys == '[SHIFT]': input_keys = u'\ue020'
	elif input_keys == '[ctrl]' or input_keys == '[CTRL]': input_keys = u'\ue021'
	elif input_keys == '[alt]' or input_keys == '[ALT]': input_keys = u'\ue022'
	elif input_keys == '[meta]' or input_keys == '[META]': input_keys = u'\ue023'
	elif input_keys == '[cmd]' or input_keys == '[CMD]': input_keys = u'\ue023'
	elif input_keys == '[win]' or input_keys == '[WIN]': input_keys = u'\ue042'

	input_keys = input_keys.replace('[shift]','').replace('[SHIFT]','')
	input_keys = input_keys.replace('[ctrl]','').replace('[CTRL]','')
	input_keys = input_keys.replace('[alt]','').replace('[ALT]','')
	input_keys = input_keys.replace('[meta]','').replace('[META]','')
	input_keys = input_keys.replace('[cmd]','').replace('[CMD]','')
	input_keys = input_keys.replace('[win]','').replace('[WIN]','')
	return input_keys

# function to output sikuli text to tagui
def output_sikuli_text ( output_text ):
	import codecs
	tagui_text = codecs.open('tagui.sikuli/tagui_sikuli.txt','w',encoding='utf8')
	tagui_text.write(output_text)
	tagui_text.close()

# function for tap / click step
def tap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - click ' + params
	if is_coordinates(params):
		return click(Location(x_coordinate(params),y_coordinate(params)))
	elif file_exists(params):
		if exists(params):
			return click(params)
		else:
			return 0
	else:
		return click(text_locator(params))

# function for rtap / rclick step
def rtap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - rclick ' + params
	if is_coordinates(params):
		return rightClick(Location(x_coordinate(params),y_coordinate(params)))
	elif file_exists(params):
		if exists(params):
			return rightClick(params)
		else:
			return 0
	else:
		return rightClick(text_locator(params))

# function for dtap / dclick step
def dtap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - dclick ' + params
	if is_coordinates(params):
		return doubleClick(Location(x_coordinate(params),y_coordinate(params)))
	elif file_exists(params):
		if exists(params):
			return doubleClick(params)
		else:
			return 0
	else:
		return doubleClick(text_locator(params))

# function for hover / move step
def hover_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - hover ' + params
	if is_coordinates(params):
		return hover(Location(x_coordinate(params),y_coordinate(params)))
	elif file_exists(params):
		if exists(params):
			return hover(params)
		else:
			return 0
	else:
		return hover(text_locator(params))

# function for type / enter step
def type_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	print '[tagui] ACTION - type ' + param1 + ' as ' + param2
	modifier_keys = modifiers_map(param2)
	param2 = keyboard_map(param2)
	if param1.endswith('page.png') or param1.endswith('page.bmp'):
		if modifier_keys == 0:
			return type(param2)
		else:
			return type(param2,modifier_keys)
	elif is_coordinates(param1):
		if modifier_keys == 0:
			return type(Location(x_coordinate(param1),y_coordinate(param1)),param2)
		else:
			return type(Location(x_coordinate(param1),y_coordinate(param1)),param2,modifier_keys)
	elif file_exists(param1):
		if exists(param1):
			if modifier_keys == 0:
				return type(param1,param2)
			else:
				return type(param1,param2,modifier_keys)
		else:
			return 0
	else:
		if modifier_keys == 0:
			return type(text_locator(param1),param2)
		else:
			return type(text_locator(param1),param2,modifier_keys)	

# function for select / choose step
def select_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' as ')].strip()
	param2 = params[4+params.find(' as '):].strip()
	print '[tagui] ACTION - click ' + param1
	if is_coordinates(param1):
		if click(Location(x_coordinate(param1),y_coordinate(param1))) == 0:
			return 0
		else:
			print '[tagui] ACTION - click ' + param2
			if is_coordinates(param2):
				return click(Location(x_coordinate(param2),y_coordinate(param2)))
			elif file_exists(param2):
				if exists(param2):
					return click(param2)
				else:
					return 0
			else:
				return click(text_locator(param2))
 
	elif file_exists(param1):
		if exists(param1):
			if click(param1) == 0:
				return 0
			else:
				print '[tagui] ACTION - click ' + param2
				if is_coordinates(param2):
					return click(Location(x_coordinate(param2),y_coordinate(param2)))
				elif file_exists(param2):
					if exists(param2):
						return click(param2)
					else:
						return 0
				else:
					return click(text_locator(param2))
		else:
			return 0

	else:
		if click(text_locator(param1)) == 0:
			return 0
		else:
			print '[tagui] ACTION - click ' + param2
			if is_coordinates(param2):
				return click(Location(x_coordinate(param2),y_coordinate(param2)))
			elif file_exists(param2):
				if exists(param2):
					return click(param2)
				else:
					return 0
			else:
				return click(text_locator(param2))

# function for reading OCR text
def read_intent ( raw_intent ):
	return text_read(raw_intent)

# function for showing OCR text
def show_intent ( raw_intent ):
	return text_read(raw_intent)

# function for saving OCR text
def save_intent ( raw_intent ):
	return text_read(raw_intent)

# function for reading text using Tesseract OCR
def text_read ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params.split(' to ')[0].strip()

	print '[tagui] ACTION - ' + raw_intent
	if is_coordinates(param1):
		region_layer = define_region(param1)
		temp_text = region_layer.text()
		output_sikuli_text(temp_text)
		return 1
	elif param1.endswith('page.png') or param1.endswith('page.bmp'):
		fullscreen_layer = Screen()
		temp_text = fullscreen_layer.text()
		output_sikuli_text(temp_text)
		return 1
	elif file_exists(param1):
		if exists(param1):
			matched_element = find(param1)
			temp_text = matched_element.text()
			output_sikuli_text(temp_text)
			return 1
		else:
			return 0
	else:
		matched_element = text_locator(param1)
		if matched_element == None:
			return 0
		else:
			temp_text = matched_element.text()
			output_sikuli_text(temp_text)
			return 1

# function for capturing screenshot
def snap_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	param1 = params[:params.find(' to ')].strip()
	param2 = params[4+params.find(' to '):].strip()
	print '[tagui] ACTION - snap ' + param1 + ' to ' + param2
	if is_coordinates(param1):
		fullscreen_layer = Screen()
		region_layer = define_region(param1)
		temp_snap_file = fullscreen_layer.capture(region_layer).getFile()
		import shutil
		shutil.copy(temp_snap_file,param2)
		return 1
	elif param1.endswith('page.png') or param1.endswith('page.bmp'):
		fullscreen_layer = Screen()
		temp_snap_file = fullscreen_layer.capture(fullscreen_layer.getBounds()).getFile()
		import shutil
		shutil.copy(temp_snap_file,param2)
		return 1
	elif file_exists(param1):
		if exists(param1):
			fullscreen_layer = Screen()
			matched_element = find(param1)
			temp_snap_file = fullscreen_layer.capture(matched_element).getFile()
			import shutil
			shutil.copy(temp_snap_file,param2)
			return 1
		else:
			return 0
	else:
		fullscreen_layer = Screen()
		matched_element = text_locator(param1)
		if matched_element == None:
			return 0
		else:
			temp_snap_file = fullscreen_layer.capture(matched_element).getFile()
			import shutil
			shutil.copy(temp_snap_file,param2)
			return 1	

# function for low-level keyboard control
def keyboard_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - keyboard ' + params
	modifier_keys = modifiers_map(params)
	params = keyboard_map(params)
	if modifier_keys == 0:
		return type(params)
	else:
		return type(params,modifier_keys)

# function for low-level mouse control
def mouse_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - mouse ' + params
	if params.lower() == 'down':
		hover(Location(Env.getMouseLocation().getX(),Env.getMouseLocation().getY()))
		mouseDown(Button.LEFT)
		return 1
	elif params.lower() == 'up':
		hover(Location(Env.getMouseLocation().getX(),Env.getMouseLocation().getY()))
		mouseUp(Button.LEFT)
		return 1
	else:
		return 0

# function for sending custom commands
def vision_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - ' + params
	exec(params,globals())
	return 1
	
def visible_intent ( raw_intent ):
	setAutoWaitTimeout(1)
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print '[tagui] ACTION - ' + raw_intent.strip()
	if exists(params):
		setAutoWaitTimeout(wait_timeout)
		return 1
	else:
		setAutoWaitTimeout(wait_timeout)
		return 0

# function to interpret input intent
def get_intent ( raw_intent ):
	if raw_intent[:4].lower() == 'tap ' or raw_intent[:6].lower() == 'click ':
		return 'tap'
	if raw_intent[:5].lower() == 'rtap ' or raw_intent[:7].lower() == 'rclick ':
		return 'rtap'
	if raw_intent[:5].lower() == 'dtap ' or raw_intent[:7].lower() == 'dclick ':
		return 'dtap'
	if raw_intent[:6].lower() == 'hover ' or raw_intent[:5].lower() == 'move ': 
		return 'hover'
	if raw_intent[:5].lower() == 'type ' or raw_intent[:6].lower() == 'enter ': 
		return 'type'
	if raw_intent[:7].lower() == 'select ' or raw_intent[:7].lower() == 'choose ':
		return 'select'
	if raw_intent[:5].lower() == 'read ' or raw_intent[:6].lower() == 'fetch ':
		return 'read'
	if raw_intent[:5].lower() == 'show ' or raw_intent[:6].lower() == 'print ':
		return 'show'
	if raw_intent[:5].lower() == 'save ':
		return 'save'
	if raw_intent[:5].lower() == 'snap ':
		return 'snap'
	if raw_intent[:9].lower() == 'keyboard ':
		return 'keyboard'
	if raw_intent[:6].lower() == 'mouse ':
		return 'mouse'
	if raw_intent[:7].lower() == 'vision ':
		return 'vision'
	if raw_intent[:8].lower() == 'visible ' or raw_intent[:8].lower() == 'present ':
		return 'visible';
	return 'error'

# function to parse and act on intent
def parse_intent ( script_line ):
	intent_type = get_intent(script_line)
	if intent_type == 'tap':
		return tap_intent(script_line)
	elif intent_type == 'rtap':
		return rtap_intent(script_line)
	elif intent_type == 'dtap':
		return dtap_intent(script_line)
	elif intent_type == 'hover':
		return hover_intent(script_line)
	elif intent_type == 'type':
		return type_intent(script_line)
	elif intent_type == 'select':
		return select_intent(script_line)
	elif intent_type == 'read':
		return read_intent(script_line)
	elif intent_type == 'show':
		return show_intent(script_line)
	elif intent_type == 'save':
		return save_intent(script_line)
	elif intent_type == 'snap':
		return snap_intent(script_line)
	elif intent_type == 'keyboard':
		return keyboard_intent(script_line)
	elif intent_type == 'mouse':
		return mouse_intent(script_line)
	elif intent_type == 'vision':
		return vision_intent(script_line)
	elif intent_type == 'visible':
		return visible_intent(script_line);
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
