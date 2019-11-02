# PYTHON INTERFACE FOR TAGUI FRAMEWORK ~ TEBEL.ORG #

# delay in seconds between scanning for inputs
scan_period = 0.1
import time

# for redirecting output to captured string
# try/except for python 2/3 compatibility
import sys
try:
	import StringIO
except ImportError:
	import io

# counter to track current tagui python step
tagui_count = '0'

# initialise and set integration interface file
tagui_output = open('tagui_py/tagui_py.txt','w')
tagui_output.write('')
tagui_output.close()

# write to interface out-file to signal ready for inputs
tagui_output = open('tagui_py/tagui_py.out','w')
tagui_output.write('[0] START')
tagui_output.close()

# initialise interface in-file before starting main loop
tagui_input = open('tagui_py/tagui_py.in','w')
tagui_input.write('')
tagui_input.close()

def py_intent ( raw_intent ):
	params = (raw_intent + ' ')[1+(raw_intent + ' ').find(' '):].strip()
	print('[tagui] ACTION - ' + params)
	tagui_output = open('tagui_py/tagui_py.txt','w')
	tagui_output.write('')
	tagui_output.close()

	old_stdout = sys.stdout
	old_stderr = sys.stderr

	# try/except for python 2/3 compatibility
	try:
		temp_output = sys.stdout = sys.stderr = StringIO.StringIO()
	except NameError:
		temp_output = sys.stdout = sys.stderr = io.StringIO()

	# try/except for user sending invalid Python code
	try:
		exec(params,globals())
		temp_result = temp_output.getvalue().strip()
	except Exception as e:
		temp_result = 'ERROR - ' + str(e)

	sys.stdout = old_stdout
	sys.stderr = old_stderr

	if temp_result != '':
		print(temp_result)
	tagui_output = open('tagui_py/tagui_py.txt','w')
	tagui_output.write(temp_result)
	tagui_output.close()
	return 1

def get_intent ( raw_intent ):
	if raw_intent[:3].lower() == 'py ':
		return 'py'
	return 'error'

def parse_intent ( script_line ):
	intent_type = get_intent(script_line)
	if intent_type == 'py':
		return py_intent(script_line)
	else:
		return 0 

# main loop to scan inputs from automation flow
print('[tagui] START  - listening for inputs'); print('')
while True:
	# scan input from run-time interface in-file
	try:
		tagui_input = open('tagui_py/tagui_py.in','r')
	except (IOError, OSError):
		print('[tagui] ERROR  - cannot open tagui_py/tagui_py.in'); print('')
		break
	tagui_intent = tagui_input.read().strip()
	tagui_input.close()

	# quit if finish signal received, initialise and repeat loop if blank
	if tagui_intent == 'finish':
		break
	elif not tagui_intent:
		tagui_count = '0'
		tagui_output = open('tagui_py/tagui_py.out','w')
		tagui_output.write('[0] START')
		tagui_output.close()
		time.sleep(scan_period)
		continue

	# get count and repeat loop if same count as last iteration
	temp_count = tagui_intent[1:tagui_intent.find('] ')].strip()
	tagui_intent = tagui_intent[2+tagui_intent.find('] '):].strip()
	if tagui_count == temp_count:
		time.sleep(scan_period)
		continue
	else:
		tagui_count = temp_count

	# otherwise parse and act on input intent
	print('[tagui] INPUT  - ' + '[' + tagui_count + '] ' + tagui_intent) 
	intent_result_value = parse_intent(tagui_intent)
	if intent_result_value == 1:
		intent_result_string = 'SUCCESS'
	else:
		intent_result_string = 'ERROR'

	# save intent_result to interface out-file
	print('[tagui] OUTPUT - ' + '[' + tagui_count + '] ' + intent_result_string); print('')
	tagui_output = open('tagui_py/tagui_py.out','w')
	tagui_output.write('[' + tagui_count + '] ' + intent_result_string)
	tagui_output.close()
	time.sleep(scan_period)

# write to interface out-file to signal finish listening
print('[tagui] FINISH - stopped listening')
tagui_output = open('tagui_py/tagui_py.out','w')
tagui_output.write('[0] FINISH')
tagui_output.close()
