<?php

require_once dirname(__file__)."/vendor/PlancakeEmailParser.php";
require_once dirname(__file__)."/../../../core/Blubber/models/BlubberPosting.class.php";

class MailProcessor {
    
    protected $mailaccount = "discussion";
    protected $delimiter = "+";
    protected $maildomain = null;
    
    static public function getInstance() {
        $processor = new MailProcessor();
        return $processor;
    }
    
    public function __construct() {
        $this->maildomain = $_SERVER['SERVER_NAME'];
        if (strtolower(substr($this->maildomain, 0, 4)) === "www.") {
            $this->maildomain = substr($this->maildomain, 4);
        }
        //init configs
    }
    
    public function sendBlubberMails(BlubberPosting $blubber) {
        
    }
    
    public function processBlubberMail($rawmail) {
        $email_regular_expression='/([-+.0-9=?A-Z_a-z{|}~])+@([-.0-9=?A-Z_a-z{|}~])+\.[a-zA-Z]{2,6}/i';
        $mail = new PlancakeEmailParser($rawmail);
        $frommail = $mail->getHeader("From");
        preg_match($email_regular_expression, $frommail, $matches);
        $frommail = $matches[0];
        
        $recipients = $mail->getTo() + $mail->getCc();
        foreach ($recipients as $recipient) {
            $me = preg_match('/'.$this->mailaccount.'['.$this->delimiter.']([^@]+)@'.$this->maildomain.'/i', $recipient, $matches);
            if ($me) {
                $thread_id = $matches[1];
            }
        }
        $thread = new BlubberPosting($thread_id);
        $author = User::findBySQL("Email = ?", array($frommail));
        $author = $author[0];
        if (!$thread->isNew() && $thread->isThread() && $author) {
            $output .= "Es ist was am Laufen.";
            //Rechtecheck TODO
            
            //Blubber hinzufügen:
            $comment = new BlubberPosting();
            $comment['description'] = $mail->getBody();
            $comment['name'] = $thread['name'];
            $comment['parent_id'] = $comment['root_id'] = $thread->getId();
            $comment['context_type'] = $thread['context_type'];
            $comment['Seminar_id'] = $thread['Seminar_id'];
            $comment['external_contact'] = 0;
            $comment['user_id'] = $author['user_id'];
            $comment->store();
         }
        return $output;
    }
}