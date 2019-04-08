https://github.com/tebelorg/Tmail
// automation flow files usually start with an URL to tell TagUI where to go
// files can also start with // for comments, or no URL if it's not web-related

// this flow goes to a GitHub page and downloads the repository file
// for issues or questions, kindly feedback on GitHub or ksoh@aisingapore.org
// see cheatsheet for steps, conditions, finding element identifiers, etc
// https://github.com/kelaberetiv/TagUI#cheat-sheet

// use read step to fetch webpage element text to variable
read (//*[@class="numbers-summary"]/li)[5] to license_type

// use check step to check a condition and print result to output
// text to put in quotation marks to differentiate from variables
check license_type equals to "MIT" | "Yes, repo is under MIT license." | "No, repo is not under MIT license."  

// for headless and visible Chrome, file can be downloaded using normal webpage interaction
// for eg, various clicking on webpage as a normal user would to trigger the file download
// alternatively, a file can be downloaded by specifying its URL as a step in flow file
// for eg, https://github.com/tebelorg/Tmail/archive/master.zip

// for headless and visible Chrome, file can be downloaded with this webpage interaction
click Clone or download
click Download ZIP

// wait for a few seconds to make sure download has time to complete
wait 10 seconds

// for Firefox and PhantomJS mode, file can be downloaded using download or receive step
// some websites including GitHub may detect and block automated downloads
// so download or receive step may not work to download file correctly

// example of download step to proactively download a file directly by specifying an URL
// download https://github.com/tebelorg/Tmail/archive/master.zip to download.zip

// example of receive step for event-based download when an URL accessed contains your keyword
// receive archive/master.zip to receive.zip

// use api step to call api, response will be saved in api_result variable
// variables can be used (eg from fetch or read step, files or self-declared)
// use `variable_name` to incorporate variables into your api step
// `variable_name` can also be used in other steps in place of text
country = "SG"
api http://services.groupkt.com/country/get/iso2code/`country`
echo api_result
echo 'Country name - ' + api_json.RestResponse.result.name
