<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Email queueing and delivering build for Pear Mail_Queue;
 * Check the README file before starting.
 *
 * @package Kohana/Emailq
 *
 */
class Kohana_Emailq {

	protected $config;


	/**
	 * Creates the Emailq object
	 *
	 * @static
	 * @return Kohana_Emailq
	 */
	public static function factory()
	{
		return new Emailq();
	}

	/**
	 * Creates the Emailq object
	 *
	 * @return void
	 */
	public function __construct()
	{
		require_once dirname(__FILE__) . '/../../swiftmailer/swift_required.php';

		$this->config = Kohana::config('emailq');
	}


    protected function lockEmail($e)
    {
        return (bool)DB::update('email_queue')
            ->set(['locked_at' => DateFormat::now()])
            ->where('locked_at', 'is', null)
            ->where('email_queueid', '=', $e->email_queueid)
            ->execute();
    }

    /**
     * Add a message to the database;
     *
     * @param $to
     * @param $cc
     * @param $bcc
     * @param $from
     * @param  $subject
     * @param  $body
     * @param array $attachments
     * @param null $direct_attachements
     * @param null $model_name
     * @param null $model_id
     * @param null $email_type
     * @return boolean - returns whether the message was added to the database.
     */
	public function add_email($to, $cc, $bcc, $from, $subject, $body, $attachments = array(),
                              $direct_attachements = NULL, $model_name = NULL, $model_id = NULL, $email_type = NULL
    ) {
		$queue = ORM::factory('emailqueue');
		$queue->to      = implode(',', (array)$to);
        $queue->cc      = implode(',', (array)$cc);
        $queue->bcc      = implode(',', (array)$bcc);
		$queue->subject = (string)$subject;
        $queue->attachements = $direct_attachements;

        if ($model_name && $model_id) {
            $queue->model_name = $model_name;
            $queue->model_id = $model_id;
        }

        if ($email_type) {
            $queue->email_type = $email_type;
        }

        if (is_array($body) and count($body) == 2) {
            $queue->body = arr::get($body, 0);
            $queue->plain_body = arr::get($body, 1);
        } else {
            $queue->body    = (string)$body;
        }

        if (is_array($from))
        {
            $queue->from_email = (string)arr::get($from, 0);
            $queue->from_name  = (string)arr::get($from, 1);
        }
        elseif ( ! empty($from))
        {
            $queue->from_email = (string)$from;
            $queue->from_name = '';
        }
        else
        {
            // Use default values from config
            $queue->from_email = AppConfig::instance()->get('from_email', 'application');
            $queue->from_name = AppConfig::instance()->get('from_name', 'application');
        }


        if ( ! $queue->save())
        {
            return FALSE;
        }

        //if any attachments were defined, add a relation (N:N type)
        //to them
        foreach ($attachments as $attachment)
        {
            //new email attachment model
            $email_queue_attachment = ORM::factory('email_queue_attachment');

            $email_queue_attachment->email_queueid = $queue->pk();

            $email_queue_attachment->reltype = $attachment->relType();
            $email_queue_attachment->relid   = $attachment->pk();

            $email_queue_attachment->save();
        }

		return $queue->pk();
	}

    /**
     * Tries to send a batch of emails, removing them from the database if it
     * succedes.
     *
     * @param int $amount - Amount of messages it will try to send per request.
     * @param null $queueid
     * @return void
     */
	public function send_emails($amount = 50, $queueid = NULL)
	{
		$config = $this->config->mail_options;

        if ($queueid)
        {
            $emails = ORM::factory('emailqueue')
                                    ->where('email_queueid', '=', $queueid)
                                    ->where('locked_at', 'is', null)
                                    ->find_all();
        }
        else
        {
            $emails = ORM::factory('emailqueue')
                                    ->limit($amount)
                                    ->where('locked_at', 'is', null)
                                    ->find_all();
        }

        if ($this->config->mail_options['driver'] == 'smtp') {
            $transport = Swift_SmtpTransport::newInstance(
                $this->config->mail_options['host'],
                $this->config->mail_options['port'],
                arr::get($this->config->mail_options, 'encryption')
            )
                ->setUsername($this->config->mail_options['username'])
                ->setPassword($this->config->mail_options['password'])
            ;
        } else {
            $transport = Swift_SendmailTransport::newInstance();
        }

		$mailer = Swift_Mailer::newInstance($transport);
                
		foreach ($emails as $e)
                {
                    // If the email is already locked it was likely sent by another thread
                    if ( ! $this->lockEmail($e)) {
                        Kohana::$log->add(Kohana::INFO, 'unable to lock email: ' . $e->id);
                        continue;
                    }

                    if ( ! empty($e->from_email))
                    {
                        if ( ! empty($e->from_name))
                        {
                            $from = array(
                                $e->from_email => $e->from_name
                            );
                        }
                        else
                        {
                            $from = $e->from_email ;
                        }
                    }
                    //jinak se nastavuje podle emailq configuraku
                    else
                    {
                        $from = array(
                            $config['sender_email'] => $config['sender_name']
                        );
                    }

                    try
                    {
                        
                        $plaintext_body = '';
                        
                        if (isset($e->plain_body)) {
                            $plaintext_body = $e->plain_body;
                        }
                        $message = Swift_Message::newInstance()
                                ->setSubject($e->subject)
                                ->setFrom($from)
                                ->setTo($e->to)
                                ->setBody($plaintext_body)
                                ->addPart($e->body, 'text/html');

                        // If cc is set - set it to message
                        if ( ! empty($e->cc)) {
                            $message->setCc($e->cc);
                        }

                        // If bcc is set - set it to message
                        if ( ! empty($e->bcc)) {
                            $message->setBcc($e->bcc);
                        }

                        //add attachment to the message
                        foreach ($e->email_queue_attachment->find_all() as $email_queue_attachment)
                        {
                            //load the target file record
                            $file_record = $email_queue_attachment->_rel;

                            //initialize Swift_Attachment by the target file
                            $swift_attachment = Swift_Attachment::fromPath($file_record->getFileDiskName());

                            //set the original file name
                            $swift_attachment->setFilename($file_record->getOriginalFilename());

                            //add to the message
                            $message->attach($swift_attachment);
                        }

                        // add "direct" attachements to the message
                        if ($e->attachements) {
                            $attachements = (array)@json_decode($e->attachements, true);
                            foreach ($attachements as $a) {
                                if (isset($a['filename'], $a['diskname'])) {

                                    //initialize Swift_Attachment by the target file
                                    $swift_attachment = Swift_Attachment::fromPath($a['diskname']);

                                    //set the original file name
                                    $swift_attachment->setFilename($a['filename']);

                                    //add to the message
                                    $message->attach($swift_attachment);
                                }
                            }
                        }

                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			$result = $mailer->send($message);
                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        
			if ($result)
                        {
                            //vymazu i vsechny zaznamy o prilohacg
                            $e->email_queue_attachment->delete_all();

                            //a nakone samotny email ve fronte
                            $e->delete();
                        }
                    }
                    catch (Exception $e)
                    {
                        Kohana::$log->add(Kohana::ERROR, 'Unable to send email due to error ":message"', array(
                            'message' => $e->getMessage()
                        ));

                        Kohana::$log->write();
                        
                        throw new Exception($e->getMessage());
                    }
		}
	}


    /**
     * Builds a table with the current queue
     *
     * @param null $class
     * @return string with an html table
     */
	public function queue_table($class = null)
	{
		$emails = ORM::factory('emailqueue')->find_all();
		$table = "<table";

		if (isset($class))
			$table .= ' class="'. $class .'"';
		$table .= "> \n";
		$table .= "<thead> \n <tr>  \n\t<th>email</th>\n\t<th>Name</th>".
				"\n\t<th>Subject</th>\n </tr>  \n</thead>  \n"; // ugly html to render with line breaks and tabs
		foreach ($emails as $e) {
			$table .= "<tr>  \n" .
					"\t <td>" . $e->email . "</td>  \n" .
					"\t <td>" . $e->name . "</td>  \n" .
					"\t <td>" . $e->subject . "</td>  \n" .
					"</tr>  \n";
		}
		$table .= "</table>  \n";
		return $table;
	}

}