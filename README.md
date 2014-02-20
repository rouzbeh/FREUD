# FREUD 
(Witty backronym:  "Facilitating Research Experience at Union Digitally")

FREUD is a simple system for managing human subjects for science experiments. It revolves around creating timeslots for experiments, and allows volunteers to sign-up for available timeslots.

The original code for FREUD was developed by Karel Simek, Jiri Matousek and Steven Stan for use at Union College. I have updated the design and some aspects of the source code.

To install, create a database and a user with all privileges to that database. Fill in the required data in required.php, and visit install.php.

## Rquirements
* PHP>5.5 (for the new password facilities)
* Mail and SMTP classes from Pear (for sending emails through an external SMTP server)
* OpenSSL libraries for PHP (for secure connections to SMTP servers)
* Mysqli (for database access)

## Original announcement 
> I am pleased to make available the source code for FREUD, Union College's in-house participant recruitment system.  Anyone is > welcome to download, use, and modify the source code.  Some important information:
> 
> FREUD can:
> 
> * Allow administrators to post, edit, and delete experiments
> * Allow experimenters to post, edit, and delete sessions
> * Allow participants to register for and cancel sessions
> 
> FREUD cannot:
> 
> * Keep track of attendance at sessions
> 
> I cannot:
> 
> * Provide any support whatsoever. I helped our ITS department design the system, but I have no knowledge of how FREUD works. 
> * Provide any guarantees as to how well the program will work.  It's been working fine here for several years, but your results may vary.  Computers are funny like that.
> 
> If you use the program, drop me a line (bizerg@union.edu) and let me know how it's working!
