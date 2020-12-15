<?php if ( ! class_exists('BounceHandler', false)) exit('No direct script access allowed');

/**
 * This file isn't used anymore, it's kept for compatibility reasons and reference.
 */
return array(
    
    BounceHandler::DIAGNOSTIC_CODE_RULES => array(
        
        /**
         * Triggered by:
         * 
         * smtp; 554 5.7.1 Message rejected under suspicion of SPAM
         */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Message rejected under suspicion of SPAM/is"
         ),
         
         /**
         * Triggered by:
         * 
         * smtp; 550 Message was not accepted
         */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Message was not accepted/is"
         ),
         
         /**
         * Triggered by:
         * 
         * Diagnostic-Code: X-Postfix; me.domain.com platform: said: 552 5.2.2 Over
         *   quota (in reply to RCPT TO command)
         * 
         * Diagnostic-Code: SMTP; 552 Requested mailbox exceeds quota.
         */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/(over|exceed).*quota/is"
         ),
         
        /**
         * Triggered by:
         * 
         * Diagnostic-Code: smtp;552 5.2.2 This message is larger than the current system limit or the recipient's mailbox is full. Create a shorter message body or remove attachments and try sending it again.
         * 
         * Diagnostic-Code: X-Postfix; host mta5.us4.domain.com.int[111.111.111.111] said:
         *   552 recipient storage full, try again later (in reply to RCPT TO command)
         * 
         * Diagnostic-Code: X-HERMES; host 127.0.0.1[127.0.0.1] said: 551 bounce as<the
         *   destination mailbox <xxxxx@yourdomain.com> is full> queue as
         *   100.1.ZmxEL.720k.1140313037.xxxxx@yourdomain.com (in reply to end of
         *   DATA command)
         */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*full/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 452 Insufficient system storage
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Insufficient system storage/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: X-Postfix; cannot append message to destination file^M
          * /var/mail/dale.me89g: error writing message: File too large^M
          * 
          * Diagnostic-Code: X-Postfix; cannot access mailbox /var/spool/mail/b8843022 for^M
          * user xxxxx. error writing message: File too large
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/File too large/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp;552 5.2.2 This message is larger than the current system limit or the recipient's mailbox is full. Create a shorter message body or remove attachments and try sending it again.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/larger than.*limit/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 451 System(u) busy, try again later.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/System.*busy/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 451 mta172.mail.tpe.domain.com Resources temporarily unavailable. Please try again later.  [#4.16.4:70].
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Resources temporarily unavailable/is"
         ),
         
          /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 451 Temporary local problem - please try later
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Temporary local problem/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 5.3.5 system config error
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/system config error/is"
         ),    
         
        // 1.3.5.4 additions
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/Connections? will not be accepted from/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spam/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spamhaus/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/OU-002/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/abuse/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/COL004-MC1F5/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/BL000010/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/SC-001/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/DNSBLs/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/IPBL1000/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/block(\s?list)?/is",
        ),
        // end 1.3.5.4 additions
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: X-Notes; User xxxxx (xxxxx@yourdomain.com) not listed in public Name & Address Book
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user)(.*)not(.*)list/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp; 450 user path no exist
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/user path no exist/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Relaying denied.
          * 
          * Diagnostic-Code: SMTP; 554 <xxxxx@yourdomain.com>: Relay access denied
          * 
          * Diagnostic-Code: SMTP; 550 relaying to <xxxxx@yourdomain.com> prohibited by administrator
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Relay.*(?:denied|prohibited)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 qq Sorry, no valid recipients (#5.1.3)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/no.*valid.*(?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 «Dªk¦a§} - invalid address (#5.5.0)
          * 
          * Diagnostic-Code: SMTP; 550 Invalid recipient: <xxxxx@yourdomain.com>
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Invalid User
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Invalid.*(?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 delivery error: dd Sorry your message to xxxxx@yourdomain.com cannot be delivered. This account has been disabled or discontinued [#102]. - mta173.mail.tpe.domain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*(?:disabled|discontinued)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 delivery error: dd This user doesn't have a domain.com account (www.xxxxx@yourdomain.com) [0] - mta134.mail.tpe.domain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/user doesn't have.*account/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 5.1.1 unknown or illegal alias: xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:unknown|illegal).*(?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 450 mailbox unavailable.
          * 
          * Diagnostic-Code: SMTP; 550 5.7.1 Requested action not taken: mailbox not available
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*(?:un|not\s+)available/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 sorry, no mailbox here by that name (#5.7.1)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/no (?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 User (xxxxx@yourdomain.com) unknown.
          * 
          * Diagnostic-Code: SMTP; 553 5.3.0 <xxxxx@yourdomain.com>... Addressee unknown, relay=[111.111.111.000]
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*unknown/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 user disabled
          * 
          * Diagnostic-Code: SMTP; 452 4.2.1 mailbox temporarily disabled: xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*disabled/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Recipient address rejected: No such user (xxxxx@yourdomain.com)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/No such (?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 MAILBOX NOT FOUND
          * 
          * Diagnostic-Code: SMTP; 550 Mailbox ( xxxxx@yourdomain.com ) not found or inactivated
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*NOT FOUND/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: X-Postfix; host m2w-in1.domain.com[111.111.111.000] said: 551
          * <xxxxx@yourdomain.com> is a deactivated mailbox (in reply to RCPT TO
          * command)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/deactivated (?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com> recipient rejected
          * ...
          * <<< 550 <xxxxx@yourdomain.com> recipient rejected
          * 550 5.1.1 xxxxx@yourdomain.com... User unknown
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*reject/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp; 5.x.0 - Message bounced by administrator  (delivery attempts: 0)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/bounce.*administrator/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <maxqin> is now disabled with MTA service.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/<.*>.*disabled/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 551 not our customer
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/not our customer/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp; 5.1.0 - Unknown address error 540-'Error: Wrong recipients' (delivery attempts: 0)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Wrong (?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp; 5.1.0 - Unknown address error 540-'Error: Wrong recipients' (delivery attempts: 0)
          * 
          * Diagnostic-Code: SMTP; 501 #5.1.1 bad address xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:unknown|bad).*(?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Command RCPT User <xxxxx@yourdomain.com> not OK
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*not OK/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 5.7.1 Access-Denied-XM.SSR-001
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Access.*Denied/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 5.1.1 <xxxxx@yourdomain.com>... email address lookup in domain map failed^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*lookup.*fail/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 User not a member of domain: <xxxxx@yourdomain.com>^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:recipient|address|email|mailbox|user).*not.*member of domain/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550-"The recipient cannot be verified.  Please check all recipients of this^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*cannot be verified/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Unable to relay for xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Unable to relay/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 xxxxx@yourdomain.com:user not exist
          * 
          * Diagnostic-Code: SMTP; 550 sorry, that recipient doesn't exist (#5.7.1)
          * Diagnostic-Code: smtp; 550-5.1.1 The email account that you tried to reach does not exist
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(alias|account|recipient|address|email|mailbox|user).*(n\'t|not)\sexist/six"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550-I'm sorry but xxxxx@yourdomain.com does not have an account here. I will not
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/not have an account/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 This account is not allowed...xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*is not allowed/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: inactive user
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/inactive.*(?:alias|account|recipient|address|email|mailbox|user)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 xxxxx@yourdomain.com Account Inactive
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*Inactive/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Recipient address rejected: Account closed due to inactivity. No forwarding information is available.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user) closed due to inactivity/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>... User account not activated
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user) not activated/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 User suspended
          * 
          * Diagnostic-Code: SMTP; 550 account expired
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*(?:suspend|expire)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 5.3.0 <xxxxx@yourdomain.com>... Recipient address no longer exists
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*no longer exist/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 VS10-RT Possible forgery or deactivated due to abuse (#5.1.1) 111.111.111.211^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:forgery|abuse)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 mailbox xxxxx@yourdomain.com is restricted
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*restrict/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: User status is locked.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*locked/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 User refused to receive this mail.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user) refused/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 501 xxxxx@yourdomain.com Sender email is not in my domain
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/sender.*not/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 Message refused
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Message (refused|reject(ed)?)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 5.0.0 <xxxxx@yourdomain.com>... No permit
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/No permit/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 sorry, that domain isn't in my list of allowed rcpthosts (#5.5.3 - chkuser)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/domain isn't in.*allowed rcpthost/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 AUTH FAILED - xxxxx@yourdomain.com^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/AUTH FAILED/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 relay not permitted^M
          * 
          * Diagnostic-Code: SMTP; 530 5.7.1 Relaying not allowed: xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/relay.*not.*(?:permit|allow)/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 not local host domain.com, not a gateway
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/not local host/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 500 Unauthorized relay msg rejected
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Unauthorized relay/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 Transaction failed
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Transaction.*fail/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp;554 5.5.2 Invalid data in message
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Invalid data/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Local user only or Authentication mechanism
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Local user only/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550-ds176.domain.com [111.111.111.211] is currently not permitted to
          * relay through this server. Perhaps you have not logged into the pop/imap
          * server in the last 30 minutes or do not have SMTP Authentication turned on
          * in your email client.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/not.*permit.*to/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Content reject. FAAAANsG60M9BmDT.1
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Content reject/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 552 MessageWall: MIME/REJECT: Invalid structure
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/MIME\/REJECT/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: smtp; 554 5.6.0 Message with invalid header rejected, id=13462-01 - MIME error: error: UnexpectedBound: part didn't end with expected boundary [in multipart message]; EOSToken: EOF; EOSType: EOF
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/MIME error/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 Mail data refused by AISP, rule [169648].
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Mail data refused.*AISP/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Host unknown
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Host unknown/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 Specified domain is not allowed.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Specified domain.*not.*allow/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: X-Postfix; delivery temporarily suspended: connect to
          * 111.111.11.112[111.111.11.112]: No route to host
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/No route to host/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 unrouteable address
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/unrouteable address/is"
         ),

         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 sender is rejected: 0,mx20,wKjR5bDrnoM2yNtEZVAkBg==.32467S2
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/sender is rejected/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 <unknown[111.111.111.000]>: Client host rejected: Access denied
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Client host rejected/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 Connection refused(mx). MAIL FROM [xxxxx@yourdomain.com] mismatches client IP [111.111.111.000].
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/MAIL FROM(.*)mismatches client IP/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 Please visit http:// antispam.domain.com/denyip.php?IP=111.111.111.000 (#5.7.1)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/denyip/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 Service unavailable; Client host [111.111.111.211] blocked using dynablock.domain.com; Your message could not be delivered due to complaints we received regarding the IP address you're using or your ISP. See http:// blackholes.domain.com/ Error: WS-02^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/client host.*blocked/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Requested action not taken: mail IsCNAPF76kMDARUY.56621S2 is rejected,mx3,BM
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/mail.*reject/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 552 sorry, the spam message is detected (#5.6.0)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/spam.*detect/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 5.7.1 Rejected as Spam see: http:// rejected.domain.com/help/spam/rejected.html
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/reject.*spam/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553 5.7.1 <xxxxx@yourdomain.com>... SpamTrap=reject mode, dsn=5.7.1, Message blocked by BOX Solutions (www.domain.com) SpamTrap Technology, please contact the domain.com site manager for help: (ctlusr8012).^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/SpamTrap/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Verify mailfrom failed,blocked
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Verify mailfrom failed/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 Error: MAIL FROM is mismatched with message header from address!
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/MAIL.*FROM.*mismatch/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 5.7.1 Message scored too high on spam scale.  For help, please quote incident ID 22492290.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/spam scale/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 550 sorry, it seems as a junk mail
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/junk mail/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 553-Message filtered. Please see the FAQs section on spam
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/message filtered/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554 5.7.1 The message from (<xxxxx@yourdomain.com>) with the subject of (SBI$#$@<K*:7s1!=l~) matches a profile the Internet community may consider spam. Please revise your message before resending.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/subject.*consider.*spam/is"
         ),
         
         /**
          * Triggered by:
          * 
          * Diagnostic-Code: SMTP; 554- (RTR:BL)
          * http://postmaster.info.aol.com/errors/554rtrbl.html 554  Connecting IP:
          * 111.111.111.111
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(\s+)?\(RTR:BL\)/is"
         ),
    ),
    
    BounceHandler::DSN_MESSAGE_RULES => array(

         /**
          * Triggered by:
          * 
          * Diagnostic-Code: X-Postfix; delivery temporarily suspended: conversation with^M
          * 111.111.111.11[111.111.111.11] timed out while sending end of data -- message may be^M
          * sent more than once
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/delivery.*suspend/is"
         ),
         
         /**
          * Triggered by:
          * 
          * This Message was undeliverable due to the following reason:
          * The user(s) account is temporarily over quota.
          * <xxxxx@yourdomain.com>
          * 
          * Recipient address: xxxxx@yourdomain.com
          * Reason: Over quota
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/over.*quota/i"
         ),
         
         /**
          * Triggered by:
          * 
          * Sorry the recipient quota limit is exceeded.
          * This message is returned as an error.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/quota.*exceeded/i"
         ),
         
         /**
          * Triggered by:
          * 
          * The user to whom this message was addressed has exceeded the allowed mailbox
          * quota. Please resend the message at a later time.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/exceed.*\n?.*quota/i"
         ),
         
         /**
          * Triggered by:
          * 
          * Failed to deliver to '<xxxxx@yourdomain.com>'
          * LOCAL module(account xxxxxx) reports:
          * account is full (quota exceeded)
          * 
          * Error in fabiomod_sql_glob_init: no data source specified - database access disabled
          * [Fri Feb 17 23:29:38 PST 2006] full error for caltsmy:
          * that member's mailbox is full
          * 550 5.0.0 <xxxxx@yourdomain.com>... Can't create output
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user).*full/i"
         ),
         
         /**
          * Triggered by:
          * 
          * gaosong "(0), ErrMsg=Mailbox space not enough (space limit is 10240KB)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/space.*not.*enough/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * xxxxx@yourdomain.com... Deferred: Connection refused by nomail.tpe.domain.com.
          * Message could not be delivered for 5 days
          * Message will be deleted from queue
          * 
          * 451 4.4.1 reply: read error from www.domain.com.
          * xxxxx@yourdomain.com... Deferred: Connection reset by www.domain.com.
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/Deferred.*Connection (?:refused|reset)/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * 451 4.0.0 I/O error
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/I\/O error/i"
         ),
         
         /**
          * Triggered by:
          * 
          * Failed to deliver to 'xxxxx@yourdomain.com'^M
          * SMTP module(domain domain.com) reports:^M
          * connection with mx1.mail.domain.com is broken^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_SOFT,
             'regex'         => "/connection.*broken/i"
         ),
         
         /**
          * Triggered by:
          * 
          *  ----- The following addresses had permanent fatal errors -----
          * <xxxxx@yourdomain.com>
          * ----- Transcript of session follows -----
          * ... while talking to mta1.domain.com.:
          * >>> DATA
          * <<< 503 All recipients are invalid
          * 554 5.0.0 Service unavailable
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/(?:alias|account|recipient|address|email|mailbox|user)(.*)invalid/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * xxxxx@yourdomain.com... Deferred: No such file or directory
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Deferred.*No such.*(?:file|directory)/i"
         ),
         
         /**
          * Triggered by:
          * 
          * Failed to deliver to '<xxxxx@yourdomain.com>'^M
          * LOCAL module(account xxxx) reports:^M
          * mail receiving disabled^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/mail receiving disabled/i"
         ),
         
         /**
          * Triggered by:
          * 
          * - These recipients of your message have been processed by the mail server:^M
          * xxxxx@yourdomain.com; Failed; 5.1.1 (bad destination mailbox address)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/bad.*(?:alias|account|recipient|address|email|mailbox|user)/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- The following addresses had permanent fatal errors -----
          * Tan XXXX SSSS <xxxxx@yourdomain..com>
          * ----- Transcript of session follows -----
          * 553 5.1.2 XXXX SSSS <xxxxx@yourdomain..com>... Invalid host name
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Invalid host name/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * xxxxx@yourdomain.com... Deferred: mail.domain.com.: No route to host
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Deferred.*No route to host/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * 550 5.1.2 xxxxx@yourdomain.com... Host unknown (Name server: .: no data known)
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Host unknown/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * 451 HOTMAIL.com.tw: Name server timeout
          * Message could not be delivered for 5 days
          * Message will be deleted from queue
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Name server timeout/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * xxxxx@yourdomain.com... Deferred: Connection timed out with hkfight.com.
          * Message could not be delivered for 5 days
          * Message will be deleted from queue
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Deferred.*Connection.*tim(?:e|ed).*out/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----
          * xxxxx@yourdomain.com... Deferred: Name server: domain.com.: host name lookup failure
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Deferred.*host name lookup failure/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- Transcript of session follows -----^M
          * 554 5.0.0 MX list for znet.ws. points back to mail01.domain.com^M
          * 554 5.3.5 Local configuration error^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/MX list.*point.*back/i"
         ),

         /**
          * Triggered by:
          * 
          * Delivery to the following recipients failed.
          * xxxxx@yourdomain.com
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Delivery to the following recipients failed/i"
         ),
         
         /**
          * Triggered by:
          * 
          * ----- The following addresses had permanent fatal errors -----^M
          * <xxxxx@yourdomain.com>^M
          * (reason: User unknown)^M
          * 
          * 550 5.1.1 xxxxx@yourdomain.com... User unknown^M
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/User unknown/i"
         ),
         
         /**
          * Triggered by:
          * 
          * 554 5.0.0 Service unavailable
          */
         array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/Service unavailable/i"
         ),
    ),
    
    BounceHandler::BODY_RULES => array(
        
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * This account is over quota and unable to receive mail.
          * 
          * <xxxxx@yourdomain.com>:
          * Warning: undefined mail delivery mode: normal (ignored).
          * The users mailfolder is over the allowed quota (size). (#5.2.2)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*\n?.*over.*quota/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          *   ----- Transcript of session follows -----
          * mail.local: /var/mail/2b/10/kellen.lee: Disc quota exceeded
          * 554 <xxxxx@yourdomain.com>... Service unavailable
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/quota exceeded.*\n?.*<(\S+@\S+\w)>/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * Hi. This is the qmail-send program at 263.domain.com.
          * <xxxxx@yourdomain.com>:
          * - User disk quota exceeded. (#4.3.0)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*quota exceeded/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * xxxxx@yourdomain.com
          * mailbox is full (MTA-imposed quota exceeded while writing to file /mbx201/mbx011/A100/09/35/A1000935772/mail/.inbox):
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/\s(\S+@\S+\w)\s.*\n?.*mailbox.*full/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * The message to xxxxx@yourdomain.com is bounced because : Quota exceed the hard limit
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/The message to (\S+@\S+\w)\s.*bounce.*Quota exceed/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * 111.111.111.111 failed after I sent the message.
          * Remote host said: 451 mta283.mail.scd.yahoo.com Resources temporarily unavailable. Please try again later [#4.16.5].
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*\n?.*Resources temporarily unavailable/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * AutoReply message from xxxxx@yourdomain.com
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_SOFT,
             'regex'            => "/^AutoReply message from (\S+@\S+\w)/i",
             'regexEmailIndex'  => 1,
         ), 
         
         // 1.3.5.4 additions
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/Connections? will not be accepted from/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spam/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spamhaus/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/OU-002/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/abuse/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/COL004-MC1F5/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/BL000010/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/SC-001/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/DNSBLs/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/IPBL1000/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/block(\s?list)?/is",
        ),
        // end 1.3.5.4 additions
         
         /**
         * Triggered by:
         * 
         * xxxxx@yourdomain.com
         * no such address here
         */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w).*\n?.*no such address here/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * 111.111.111.111 does not like recipient.
          * Remote host said: 550 User unknown
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*\n?.*user unknown/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * Sorry, no mailbox here by that name. vpopmail (#5.1.1)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*no mailbox/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * xxxxx@yourdomain.com<br>
          * local: Sorry, can't find user's mailbox. (#5.1.1)<br>
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w)<br>.*\n?.*\n?.*can't find.*mailbox/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * (reason: Can't create output)
          * (expanded from: <xxxxx@yourdomain.com>)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/Can't create output.*\n?.*<(\S+@\S+\w)>/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * ????????????????:
          * xxxxx@yourdomain.com : ????, ?????.
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w).*=D5=CA=BA=C5=B2=BB=B4=E6=D4=DA/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * xxxxx@yourdomain.com
          * Unrouteable address
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w).*\n?.*Unrouteable address/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * Delivery to the following recipients failed.
          * xxxxx@yourdomain.com
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/delivery[^\n\r]+failed\S*\s+(\S+@\S+\w)\s/is",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * A message that you sent could not be delivered to one or more of its^M
          * recipients. This is a permanent error. The following address(es) failed:^M
          * ^M
          * xxxxx@yourdomain.com^M
          * unknown local-part "xxxxx" in domain "yourdomain.com"^M
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w).*\n?.*unknown local-part/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:^M
          * 111.111.111.11 does not like recipient.^M
          * Remote host said: 550 Invalid recipient: <xxxxx@yourdomain.com>^M
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/Invalid.*(?:alias|account|recipient|address|email|mailbox|user).*<(\S+@\S+\w)>/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * Sent >>> RCPT TO: <xxxxx@yourdomain.com>^M
          * Received <<< 550 xxxxx@yourdomain.com... No such user^M
          * ^M
          * Could not deliver mail to this user.^M
          * xxxxx@yourdomain.com^M
          * *****************     End of message     ***************^M
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/\s(\S+@\S+\w).*No such.*(?:alias|account|recipient|address|email|mailbox|user)>/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:^M
          * This address no longer accepts mail.
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*(?:alias|account|recipient|address|email|mailbox|user).*no.*accept.*mail>/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * xxxxx@yourdomain.com<br>
          * 553 user is inactive (eyou mta)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w)<br>.*\n?.*\n?.*user is inactive/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * xxxxx@yourdomain.com [Inactive account]
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/(\S+@\S+\w).*inactive account/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * Unable to switch to /var/vpopmail/domains/domain.com: input/output error. (#4.3.0)
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*input\/output error/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * can not open new email file errno=13 file=/home/vpopmail/domains/fromc.com/0/domain/Maildir/tmp/1155254417.28358.mx05,S=212350
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*can not open new email file/i",
             'regexEmailIndex'  => 1,
         ),
         
         /**
          * Triggered by:
          * 
          * <xxxxx@yourdomain.com>:
          * The user does not accept email in non-Western (non-Latin) character sets.
          */
         array(
             'bounceType'       => BounceHandler::BOUNCE_HARD,
             'regex'            => "/<(\S+@\S+\w)>.*\n?.*does not accept[^\r\n]*non-Western/i",
             'regexEmailIndex'  => 1,
         ),
    ),
    
    /**
     * Following are generic rules that should be applied at the end of the checks.
     * 
     */
    BounceHandler::COMMON_RULES => array(
        
        /**
         * Triggered by:
         * 
         * user has Exceeded
         * exceeded storage allocation
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/(user\shas\s)?exceeded(\s+storage\sallocation)?/i",
        ),
        
        /**
         * Triggered by:
         * 
         * Mailbox full
         * mailbox is full
         * Mailbox quota usage exceeded
         * Mailbox size limit exceeded
         **/
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/mail(box|folder)(\s+)?(is|full|quota|size)(\s+)?(full|usage|limit)?(\s+)?(exceeded)?/i",
        ),
        
        /**
         * Triggered by:
         * 
         * Quota full
         * Quota violation
         **/
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/quota\s(full|violation)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * User has exhausted allowed storage space
         * User mailbox exceeds allowed size
         * User has too many messages on the server
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/User\s(has|mail(box|folder))\s+((exhausted|exceeds)\sallowed\s(size|.*space)|(too\smany.*server))/i",
        ),
        
        /**
         * Triggered by:
         * 
         * delivery temporarily suspended
         * Delivery attempts will continue to be made for
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/delivery\s(temporarily\ssuspended|attempts\swill\scontinue\sto\sbe\smade\sfor)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * Greylisting in action
         * Greylisted for 5 minutes
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/greylist(ing|ed)\s(in|for)\s(\w+(\sminutes)?)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * Server busy
         * server too busy
         * system load is too high
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/(server|system)\s(load\sis\s)?(too\s)?(busy|high)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * too busy to accept mail
         * too many connections
         * too many sessions
         * Too much load
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/too\s(busy|many|much)\s(to\saccept\smail|connections?|sessions?|load)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * temporarily deferred
         * temporarily unavailable
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/temporarily\s(deferred|unavailable)/i",
        ),
        
        /**
         * Triggered by:
         * 
         * Try later
         * retry timeout exceeded
         * queue too long
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/try\slater|retry\stimeout\sexceeded|queue\stoo\slong/i",
        ),

        // box full
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/Benutzer\shat\szuviele\sMails\sauf\sdem\sServer/i",
        ),
        
        /**
         * Triggered by:
         * 
         * rate limited
         * unsolicited mail originating
         * IP address has been temporarily
         */
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/rate\slimited|unsolicited\smail|IP\saddress\shas\sbeen\stemporarily\s/i",
        ),
        
        // since 1.3.4.9
        array(
            "bounceType"    => BounceHandler::BOUNCE_SOFT,
            "regex"         => array(
                "/Message rejected under suspicion of SPAM/i",
                "/Message was not accepted/i",
				"/user mailbox is inactive/i",
				"/user account disabled/i",
				"/this account has been disabled or discontinued/i",
				"/user account is expired/i",
				"/User is inactive/i",
				"/inactive user/i",
				"/extended inactivity new mail is not currently being accepted/i",
				"/Sorry, I wasn't able to establish an SMTP connection/i",
				"/message refused/i",
				"/permission denied/i",
				"/mailbox temporarily disabled/i",
				"/Blocked address/i",
				"/Account inactive as unread/i",
				"/Account inactive/i",
				"/account expired/i",
				"/User hasn't entered during last/i",
				"/Account closed due to inactivity/i",
				"/This account is not allowed/i",
				"/Mailbox_currently_suspended/i",
				"/Mailbox disabled/i",
				"/quota exceeded/i",
				"/user is over quota/i",
				"/exceeds size limit/i",
				"/user has full mailbox/i",
				"/Mailbox disk quota exceeded/i",
				"/over the allowed quota/i",
				"/User mailbox exceeds allowed size/i",
				"/does not have enough space/i",
				"/mailbox is full/i",
				"/Can't create output/i",
				"/mailbox full/i",
				"/File too large/i",
				"/too many messagens on this mailbox/i",
				"/too many messages on this mailbox/i",
				"/too many messages in this mailbox/i",
				"/Not enough storage space/i",
				"/Over quota/i",
				"/over the maximum allowed number of messages/i",
				"/Recipient exceeded email quota/i",
				"/temporarily deferred/i",
				"/is FULL/i",
				"/Quota exceeded/i",
				"/The user has not enough diskspace available/i",
				"/Mailbox has exceeded the limit/i",
				"/exceeded storage allocation/i",
				"/Quota violation/i",
				"/522_mailbox_full/i",
				"/account is full/i",
				"/incoming mailbox for user/i",
				"/message would exceed quota/i",
				"/recipient exceeded dropfile size quota/i",
				"/not able to receive any more mail/i",
				"/user is invited to retry/i",
				"/User account is overquota/i",
				"/mailfolder is full/i",
				"/exceeds allowed message count/i",
				"/message is larger than the space available/i",
				"/recipient storage full/i",
				"/The user's space has used up\./i",
				"/user is over their quota/i",
				"/exceed the quota for the mailbox/i",
				"/exceed maximum allowed storage/i",
				"/Inbox is full/i",
				"/over quota/i",
				"/maildir has overdrawn his diskspace quota/i",
				"/disk full/i",
				"/Quota exceed/i",
				"/Storage quota reached/i",
				"/user overdrawn his diskspace quota/i",
				"/exceeded his\/her quota/i",
				"/quota for the mailbox/i",
				"/The incoming mailbox for user/i",
				"/exceeded the space quota/i",
				"/mail box space not enough/i",
				"/insufficient disk space/i",
				"/over their disk quota/i",
				"/Message would exceed/i",
				"/User is overquota/i",
				"/Requested mailbox exceeds quota/i",
				"/exceed mailbox quota/i",
				"/over the storage quota/i",
				"/over disk quota/i",
				"/mailbox_quota_exceeded/i",
				"/Status: 5\.2\.2/i",
				"/over the maximum allowed mailbox size/i",
				"/Delivery failed: Over quota/i",
				"/errno\=28/i",
				"/Your e\-mail was rejected for policy reasons on this gateway/i",
				"/550 Protocol violation/i",
				"/Blacklisted/i",
				"/is refused\. See http:\/\/spamblock\.outblaze\.com/i",
				"/550 Rule imposed mailbox access for/i",
				"/Message cannot be accepted, content filter rejection/i",
				"/Mail appears to be unsolicited/i",
				"/rejected for policy reasons/i",
				"/Spam rejected/i",
				"/Error: content rejected/i",
				"/Message Denied: Restricted attachment/i",
				"/Denied by policy/i",
				"/has exceeded maximum attachment count limit/i",
				"/Blocked for spam/i",
				"/Blocked for abuse/i",
				"/Message held for human verification/i",
				"/considered unsolicited bulk e\-mail/i",
				"/message held before permitting delivery/i",
				"/envelope sender is in my badmailfrom/i",
				"/listed in multi\.surbl\.org/i",
				"/black listed url host/i",
				"/this message scored/i",
				"/on spam scale/i",
				"/message filtered/i",
				"/rejected as bulk/i",
				"/message content rejected/i",
				"/Mail From IP Banned/i",
				"/Connection refused due to abuse/i",
				"/mail server is currently blocked/i",
				"/Spam origin/i",
				"/extremely high on spam scale/i",
				"/is not accepting mail from this sender/i",
				"/spamblock/i",
				"/blocked using/i",
				"/HTML tag unacceptable/i",
				"/appears to be spam/i",
				"/not accepting mail with attachments or embedded images/i",
				"/message contains potential spam/i",
				"/You have been blocked by the recipient/i",
				"/message looks like spam/i",
				"/message looks like a spam/i",
				"/Message contains unacceptable attachment/i",
				"/high spam probability/i",
				"/email is considered spam/i",
				"/Spam detected/i",
				"/Message identified as SPAM/i",
				"/blocked because it contains FortiGuard \- AntiSpam blocking URL/i",
				"/This message has been blocked because it contains FortiSpamshield blocking URL/i",
				"/Sender is on domain's blacklist/i",
				"/This message does not comply with required standards/i",
				"/Message rejected because of unacceptable content/i",
				"/554 Transaction failed/i",
				"/5\.7\.1 reject content/i",
				"/5\.7\.1 URL\/Phone Number Filter/i",
				"/5\.7\.1 Message cannot be accepted, spam rejection/i",
				"/Mail contained a URL rejected by SURBL/i",
				"/This message has been flagged as spam/i",
				"/they are not accepting mail/i",
				"/550 POSSIBLE SPAM/i",
				"/headers consistent with spam/i",
				"/5\.7\.1 Content\-Policy reject/i",
				"/rejected by an anti\-spam/i",
				"/rejected by anti\-spam/i",
				"/is on RBL list/i",
				"/sender denied/i",
				"/Your message was rejected because it appears to be part of a spam bomb/i",
				"/it is spam/i",
				"/5\.7\.1 bulkmail/i",
				"/Message detected as spam/i",
				"/5\.7\.1 Blocked/i",
				"/identified SPAM/i",
				"/Error: SPAM/i",
				"/message is banned/i",
				"/junk mail/i",
				"/bulk mail rejected/i",
				"/SPAM not accepted/i",
				"/rejected By DCC/i",
				"/Spam Detector/i",
				"/5\.7\.1 Message rejected/i",
				"/5\.7\.1 Rejected as SPAM/i",
				"/Message rejected due to the attachment filtering policy/i",
				"/Message rejected due to content restrictions/i",
				"/Spam is not allowed/i",
				"/Blocked by policy/i",
				"/content filter/i",
				"/spam filter/i",
				"/filter rejection/i",
				"/rejected by spam\-filter/i",
				"/Forbidden for policy reasons/i",
				"/looked like SPAM/i",
				"/Message blocked/i",
				"/not delivered for policy reasons/i",
				"/high on spam/i",
				"/5\.7\.1 Rejected \- listed at/i",
				"/invalid message content/i",
				"/550 This message scored/i",
				"/Blocked by SPAM/i",
				"/This message has been blocked/i",
				"/SURBL filtered by/i",
				"/message classified as bulk/i",
				"/554 Message rejected/i",
				"/mail rejected for spam/i",
				"/554 5\.7\.1/i",
				"/message that you send was considered spam/i",
				"/message that you sent was considered spam/i",
				"/554 5\.7\.0 Reject/i",
				"/550 Spam/i",
				"/Message rejected/i",
				"/550 Rejected/i",
				"/Message rejected: Conversion failure/i",
				"/Sorry, message looks lik/i",
				"/email has been identified as SPAM/i",
				"/possible spam/i",
				"/550 Content Rejected/i",
				"/Message not allowed by spam/i",
				"/has been quarantined/i",
				"/blocked as spam/i",
				"/a stray CR character/i",
				"/no longer accepts messages with/i",
				"/DNSBL:To request removal of/i",
				"/won't accept this email/i",
				"/Rejected by filter processing/i",
				"/marked by Telerama as SPAM/i",
				"/triggered a spam block/i",
				"/Message classified as spam by Bogofilter/i",
				"/http:\/\/postmaster\.info\.aol\.com\/errors\/421dynt1\.html/i",
				"/Spam limit has been reached/i",
				"/One of the words in the message is blocked/i",
				"/Your email has been automatically rejected/i",
				"/message from policy patrol email filtering/i",
				"/blocked by filter rules/i",
				"/Mail rejected by Windows Live Hotmail for policy reasons/i",
				"/542 Rejected/i",
				"/Remote sending only allowed with authentication/i",
				"/550 authentication required/i",
				"/sorry, that domain isn't in my list of allowed rcpthosts/i",
				"/has installed an invalid MX record with an IP address instead of a domain name on the right hand side\./i",
				"/all relevant MX records point to non\-existent hosts/i",
				"/not capable to receive mail/i",
				"/CNAME lookup failed temporarily/i",
				"/TLS connect failed: timed out/i",
				"/timed out while receiving the initial server greeting/i",
				"/malformed or unexpected name server reply/i",
				"/unreachable for too long/i",
				"/Please receive your mail before sending/i",
				"/but connection died/i",
				"/Failed; 4\.4\.7 \(delivery time expired\)/i",
				"/unable to connect successfully to the destination mail server/i",
				"/This message is looping/i",
				"/Connection timed out/i",
				"/failed on DATA command/i",
				"/Can't open mailbox/i",
				"/Delivery failed 1 attempt/i",
				"/Hop count exceeded/i",
				"/Command rejected/i",
				"/Unable to create a dot\-lock/i",
				"/Command died with status/i",
				"/550 System error/i",
				"/Connection refused/i",
				"/Command time limit exceeded/i",
				"/Resources temporarily unavailable/i",
				"/error on maildir delivery/i",
				"/this message has been in the queue too long/i",
				"/loops back to myself/i",
				"/temporary failure/i",
				"/temporary problem/i",
				"/Temporary error on maildir delivery/i",
				"/The host does not have any mail exchanger/i",
				"/5\.7\.1 Transaction failed/i",
				"/delivery temporarily suspended/i",
				"/Undeliverable message/i",
				"/user path no exist/i",
				"/user path does not exist/i",
				"/maildir delivery failed/i",
				"/Resources temporarily not available/i",
				"/has exceeded the max emails per hour/i",
				"/several matches found in domino/i",
				"/internal software error/i",
				"/internal server error/i",
				"/cannot store document/i",
				"/delivery time expired/i",
				"/delivery expired \(message too old\)/i",
				"/operation timed out/i",
				"/4\.3\.2 service shutting down/i",
				"/loop count exceeded/i",
				"/unable to deliver a message to/i",
				"/delivery was refused/i",
				"/Too many results returned/i",
				"/Error in processing/i",
				"/Error opening input\/output file/i",
				"/SC\-001 Mail rejected by Windows Live Hotmail for policy reasons\./i",
				"/Remote host said: 542 Rejected/i",
				"/Remote host said: 554 Failure/i",
				"/Could not complete sender verify callout/i",
				"/Sender verification error/i",
				"/Mail only accepted from IPs with valid reverse lookups/i",
				"/lost connection with/i",
				"/sender id \(pra\) not permitted/i",
				"/could indicate a mail loop/i",
				"/but sender was rejected/i",
				"/Address does not pass the Sender Policy Framework/i",
				"/only accepts mail from known senders/i",
				"/Name service error/i",
				"/You will need to add a PTR record \(also known as reverse lookup\) before you are able to send email into the iiNet network\./i",
				"/does not have a valid PTR record associated with it\./i",
				"/refused to talk to me: 452 try later/i",
            ),
        ),
        
        // 1.3.5.4 additions
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/Connections? will not be accepted from/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spam/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/spamhaus/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/OU-002/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/abuse/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/COL004-MC1F5/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/BL000010/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/SC-001/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/DNSBLs/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/IPBL1000/is",
        ),
        array(
            'bounceType'    => BounceHandler::BOUNCE_SOFT,
            'regex'         => "/block(\s?list)?/is",
        ),
        // end 1.3.5.4 additions
        
        /**
         * Triggered by:
         * 
         *  This is the mail system at host mail.host.com.
         *
         *  I'm sorry to have to inform you that your message could not
         *  be delivered to one or more recipients. It's attached below.
         *   
         *  For further assistance, please send mail to postmaster.
         *   
         *  If you do so, please include this problem report. You can
         *  delete your own text from the attached returned message.
         */
        array(
             'bounceType'    => BounceHandler::BOUNCE_HARD,
             'regex'         => "/sorry\sto\shave\sto\sinform\syou\sthat\syour\smessage\scould\snot/six"
        ),
        
        // unknown user
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/destin\.\sSconosciuto/i",
        ),
        
        // unknown
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Destinatario\serrato/i",
        ),
        
        // unknown
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Destinatario\ssconosciuto\so\smailbox\sdisatttivata/i",
        ),
        
        // unknown
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Indirizzo\sinesistente/i",
        ),
        
        // unknown
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/nie\sistnieje/i",
        ),
        
        // unknown
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Nie\sma\stakiego\skonta/i",
        ),
        
        // expired
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Esta\scasilla\sha\sexpirado\spor\sfalta\sde\suso/i",
        ),
        
        // disabled
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Adressat\sunbekannt\soder\sMailbox\sdeaktiviert/i",
        ),
        
        // disabled
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Destinataire\sinconnu\sou\sboite\saux\slettres\sdesactivee/i",
        ),
        
        // inactive
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/El\susuario\sesta\sen\sestado:\sinactivo/i",
        ),
        
        // inactive
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Podane\skonto\sjest\szablokowane\sadministracyjnie\slub\snieaktywne/i",
        ),
        
        // inactive
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Questo\sindirizzo\se'\sbloccato\sper\sinutilizzo/i",
        ),
        
        // spam
        array(
            'bounceType'    => BounceHandler::BOUNCE_HARD,
            'regex'         => "/Wiadomosc\szostala\sodrzucona\sprzez\ssystem\santyspamowy/i",
        ),
        
        // since 1.3.4.9
        array(
            "bounceType"    => BounceHandler::BOUNCE_HARD,
            "regex"         => array(
				"/was not delivered to/i",
				"/This is a permanent error/i",
				"/Remote host said: 550 5\.1\.1 No such user/i",
				"/This is a permanent error; I've given up\. Sorry it didn't work out\./i",
				"/PERM_FAILURE:/i",
				"/User unknown/i",
				"/UNKNOWN_USER: No such user/i",
				"/mailbox unavailable/i",
				"/Requested action not taken: mailbox unavailable/i",
				"/Action: failed/i",
				"/User unknown in virtual alias table/i",
				"/invalid mailbox/i",
				"/couldn't find any host named/i",
				"/invalid address/i",
				"/user unknown/i",
				"/this user doesn't have a yahoo\.com account/i",
				"/permanent fatal errors/i",
				"/No mailbox here by that name/i",
				"/User not known/i",
				"/Remote host said: 553/i",
				"/No such user/i",
				"/No such recipient/i",
				"/unknown user/i",
				"/mailbox not found/i",
				"/No such user here/i",
				"/Delivery to the following recipients failed/i",
				"/unknown or illegal alias/i",
				"/not listed in domino directory/i",
				"/unrouteable address/i",
				"/Destination server rejected recipients/i",
				"/unable to validate recipient/i",
				"/No such virtual user here/i",
				"/The recipient cannot be verified/i",
				"/bad address/i",
				"/Recipient unknown/i",
				"/mailbox is currently unavailable/i",
				"/Invalid User/i",
				"/recipient rejected/i",
				"/invalid recipient/i",
				"/not our customer/i",
				"/Unknown account/i",
				"/This user doesn't have a/i",
				"/no users here by that name/i",
				"/account closed/i",
				"/user not found/i",
				"/This address no longer accepts mail/i",
				"/does not like recipient/i",
				"/Delivery to the following recipient failed permanently/i",
				"/User Does Not Exist/i",
				"/The mailbox is not available on this system/i",
				"/mailbox (.*) does not exist/i",
				"/not a valid mailbox/i",
				"/server doesn't handle mail for that user/i",
				"/No such account/i",
				"/unknown recipient/i",
				"/user invalid/i",
				"/User reject the mail/i",
				"/The following recipients are unknown/i",
				"/name or service not known/i",
				"/I couldn't find any host named/i",
				"/message could not be delivered for \d+ days/i",
				"/I couldn't find a mail exchanger or IP address/i",
				"/address does not exist/i",
				"/relaying denied/i",
				"/access denied/i",
				"/554 denied/i",
				"/they are not accepting mail from/i",
				"/Relaying not allowed/i",
				"/not permitted to relay through this server/i",
				"/Sender verify failed/i",
				"/Although I'm listed as a best\-preference MX or A for that host/i",
				"/mail server permanently rejected message/i",
				"/bad address syntax/i",
				"/delivery failed; will not continue trying/i",
				"/No DNS information was found/i",
				"/Mailaddress is administratively disabled/i",
				"/Mailbox currently suspended/i",
				"/Account has been suspended/i",
				"/account is not active/i",
				"/recipient never logged onto/i",
				"/is disabled/i",
				"/account has been temporarily suspended/i",
				"/deactivated mailbox/i",
				"/disabled due to inactivity/i",
				"/not an active address/i",
				"/inactive on this domain/i",
				"/Status: 5\.2\.1/i",
				"/said: 550 5\.2\.1/i",
				"/account is locked/i",
				"/account deactivated/i",
				"/disabled mailbox/i",
				"/Mailaddress is administrativley disabled/i",
				"/unavailable to take delivery of the message/i",
				"/550 5\.1\.1 User unknown/i",
				"/said: 553 sorry,/i",
				"/does not exist/i",
				"/User unknown in virtual mailbox/i",
				"/User is unknown/i",
				"/Unrouteable address/i",
				"/This address does not receive mail/i",
				"/Recipient no longer on server/i",
				"/retry timeout exceeded/i",
				"/retry time not reached for any host after a long failure period/i",
				"/unknown address or alias/i",
				"/\> does not exist/i",
				"/Recipient address rejected/i",
				"/Recipient not allowed/i",
				"/Address rejected/i",
				"/Address invalid/i",
				"/Unknown local part/i",
				"/Unknown local\-part/i",
				"/mail receiving disabled/i",
				"/bad destination email address/i",
				"/deactivated due to abuse/i",
				"/no such address/i",
				"/user_unknown/i",
				"/recipient not found/i",
				"/User unknown in local recipient table/i",
				"/This recipient e\-mail address was not found/i",
				"/no valid recipients/i",
				"/This user doesn't have a yahoo/i",
				"/mailbox not available/i",
				"/not a valid user/i",
				"/Unknown destination address/i",
				"/Unknown address error/i",
				"/recipient's account is disabled/i",
				"/Unable to chdir to maildir/i",
				"/undeliverable to the following/i",
				"/invalid domain mailbox user/i",
				"/Permanent error in automatic homedir creation/i",
				"/Invalid or unknown virtual user/i",
				"/Your e\-mail has not been delivered/i",
				"/Your email has not been delivered/i",
				"/Your mail has not been delivered/i",
				"/Not a valid recipient/i",
				"/Please check the recipients e\-mail address/i",
				"/email has changed/i",
				"/This address is no longer valid/i",
				"/unknown email address/i",
				"/no longer in use/i",
				"/not have a final email delivery point/i",
				"/non esiste/i",
				"/no recipients/i",
				"/permanent fatal delivery/i",
				"/address is not valid/i",
				"/unavailable mailbox/i",
				"/550 5\.1\.1/i",
				"/Status: 5\.1\.1/i",
				"/account does not exist/i",
				"/The recipient name is not recognized/i",
				"/can't create user output file/i",
				"/no such user here/i",
				"/There is no user by that name/i",
				"/No such mailbox/i",
				"/not a recognised email account/i",
				"/address is no longer active/i",
				"/This is a permanent error\. The following address/i",
				"/Unable to find alias user/i",
				"/sorry, no mailbox/i",
				"/doesn't have an account/i",
				"/not a valid email account/i",
				"/I have now left/i",
				"/I am no longer with/i",
				"/Invalid final delivery user/i",
				"/no longer available/i",
				"/unknown address/i",
				"/isn't in my list of allowed recipients/i",
				"/recipients are invalid/i",
				"/recipient is invalid/i",
				"/mailbox is not valid/i",
				"/invalid e\-mail address/i",
				"/doesn't_have_a_yahoo/i",
				"/not known at this site/i",
				"/email name is not found/i",
				"/address doesn't exist/i",
				"/destination addresses were unknown/i",
				"/no existe/i",
				"/does not have an email/i",
				"/_does_not_exist_here/i",
				"/User unknown in virtual mailbox table/i",
				"/user is no longer available/i",
				"/unknown user account/i",
				"/Addressee unknown/i",
				"/This Gmail user does not exist/i",
				"/554 delivery error: This user doesn't have/i",
				"/No such domain at this location/i",
				"/an MX or SRV record indicated no SMTP service/i",
				"/I couldn't find any host by that name/i",
				"/Domain does not exist; please check your spelling/i",
				"/Domain not used for mail/i",
				"/Domain must resolve/i",
				"/unrouteable mail domain/i",
				"/no route to host/i",
				"/host not found/i",
				"/Host or domain name not found/i",
				"/illegal host\/domain/i",
				"/bad destination host/i",
				"/no matches to nameserver query/i",
				"/no such domain/i",
				"/Cannot resolve the IP address of the following domain/i",
				"/too many hops, this message is looping/i",
				"/loop: too many hops/i",
				"/relay not permitted/i",
				"/This mail server requires authentication when attempting to send to a non\-local e\-mail address\./i",
				"/is currently not permitted to relay/i",
				"/Unable to relay for/i",
				"/not a gateway/i",
				"/This system is not configured to relay mail/i",
				"/we do not relay/i",
				"/relaying mail to/i",
				"/Relaying is prohibited/i",
				"/Cannot relay/i",
				"/relaying disallowed/i",
				"/Authentication required for relay/i",
				"/5\.7\.1 Unable to deliver to/i",
				"/message could not be delivered/i",
				"/dns loop/i",
				"/domain missing or malformed/i",
				"/550_Invalid_recipient/i",
				"/Invalid Address/i",
				"/Bad destination mailbox address/i",
            ),
        ),
    ),
);