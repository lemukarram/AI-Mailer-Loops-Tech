1: i want to setup a master gemini api key in the admin panel that do only these things: write a best place holder email for prompt based on       
   the user profile information and user setting user bio and user resume,      
   when user loads the campaign page there should be button fill inofrmation that will run the ai agent will check if that user       
   has already setup profile then call the ai agent gemini and write a          
   perfect email with perfect subject user [company] place holder in both       
   subject and body and write signature based on user profile information,      
   also write the best placeholder prompt based on user profile information.    
   if the profile informatiom is not setup then write a generic prompt in       
   prompt area and generic job hut email in menual email area again use         
   [company] placeholder in both body and subject.
   for every type of writting always remember that 100% humanize tone and no use of ai characters so that everthinng should look very natural. 
   only one ai call allowed by user by using this master gemini api key. 
   otherwise all over the system user will use his own api key.

2- once the user activate and he is login for the first time the system will perform a kind of wizard setup with clearn asthetic UI/UX animation transitions and icons.
first screen show only two option as ask the user he want to use this software for job hunt or for business leads. then on the next screen based on previous user input show profile section where ask about the user or ask about the business, ask the designation or services, ask the about us or about your company. 
base on the previous decisions on the third screen ask about the file upload , resume upload of emal attachment busines profile upload. 
on the next screen 4th screen: a button button attach you gmail account with system [here perform proper using OAuth 2.0 ask the permisions to use send emails from gmail] after that we will use gmail api to send email for that user. unnder that button show an smtp setting form for manually configurating the smtp email with the system. on the last 5th screen ask the settings like AI email generator by default it is off , if the user turn it on ask for the model ai model gemini or open ai and then ask for the api key for selected model. if your select AI email generator use the pervious instruction and user data along with attachment and write her a best customized prompt for writing ai emails and display ai email prompt area. if user does not turn on the ai email generator then based on the user information and attachment write a best sample email with subject and body that uses [company] placeholder and manual email form display here. on this screen also ask for per hour email limit [display instruction like for best reach and not spammig keep per hour email under 10 and 12 is the system max limit per hour] [wizard completed]

3-base on the user choice show the field names in campaing and other palaces accordingly and setup the master prompt place holder + email place holder accordingly in the campaing page for job seeker and for leads gennerator.

build and modify it step by step verify each step then move to next step so that 100% pefect system will get ready. 

do not deploy that code on server. just push the changes on github on main branch after each step verification.
   