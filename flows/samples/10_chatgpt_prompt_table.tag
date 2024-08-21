// The aim of this tagfile is to help you save you prompt and answer to a excel table

// Put the url of your chatgpt  windows you just chat
https://chat.openai.com/c/4c7df66f-251a-4a10-985e-98f7e68ff0a0
//First time the use of the file,u need to login your account.So need to wait 100s.
//After u login in,just remove the wait line
//wait 100

csv_header='"no","Prompt","Answer"'
//prompt_answer_table.csv is the name of your prompt table
dump `csv_header` to prompt_answer_table_chatgpt.csv
// If you want to save more than 100 records, you can change the number 100 to a bigger number
// If you don't need to see the prompt and answer, you can delete the two lines of echo below
for (n=2; n<=100; n=n+2)
{
  read //*[@id="__next"]/div[2]/div[2]/div/main/div[2]/div/div/div/div[`n`]/div/div[2]/div[1] to prompt
  echo `prompt`
  read //*[@id="__next"]/div[2]/div[2]/div/main/div[2]/div/div/div/div[`n`+1]/div/div[2]/div[1] to answer
  echo `answer`
  record='"'+n+'","'+prompt+'","'+answer+'"'
  write `record` to prompt_answer_table_chatgpt.csv
}

