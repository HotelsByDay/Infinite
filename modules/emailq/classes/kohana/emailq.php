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
		return new Kohana_Emailq();
	}

	/**
	 * Creates the Emailq object
	 *
	 * @return void
	 */
	public function Kohana_Emailq()
	{
		require_once MODPATH . 'emailq/swiftmailer/swift_required.php';
		$this->config = Kohana::config('emailq');
		//print_r($this->config);
	}

	/**
	 * Add a message to the database;
	 *
	 * @param  $email
	 * @param  $name
	 * @param  $subject
	 * @param  $body
	 * @return boolean - returns wether the message was added to the database.
	 */
	public function add_email($email, $from, $subject, $body, $attachments = array())
	{
		$queue = ORM::factory('emailqueue');
		$queue->email = $email;
		$queue->subject = $subject;
		$queue->body = $body;

                if (is_array($from))
                {
                    $queue->from_email = arr::get($from, '0');
                    $queue->from_name  = arr::get($from, '1');
                }
                else
                {
                    $queue->from_email = $from;
                    $queue->from_name = '';
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
	 * @return void
	 */
	public function send_emails($amount = 50, $queueid = NULL)
	{
		$config = $this->config->mail_options;

                if ($queueid)
                {
                    $emails = ORM::factory('emailqueue')
                                    ->where('email_queueid', '=', $queueid)
                                    ->find_all();
                }
                else
                {
                    $emails = ORM::factory('emailqueue')
                                    ->limit($amount)
                                    ->find_all();
                }

		$transport = Swift_SmtpTransport::newInstance(
				$this->config->mail_options['host'],
				$this->config->mail_options['port'],
                                arr::get($this->config->mail_options, 'encryption'))->setUsername($this->config->mail_options['username'])
                                                                                    ->setPassword($this->config->mail_options['password']);

		$mailer = Swift_Mailer::newInstance($transport);
                
		foreach ($emails as $e)
                {
                    //poud je u emailu definovan email odesilatele, tak se bude
                    //nastavovat custo odesilate (nikoliv podle configu emailq)
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
			$message = Swift_Message::newInstance()
					->setSubject($e->subject)
					->setFrom($from)
					->setTo($e->email)
					->setBody($e->body)
					->addPart($e->body, 'text/html');

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

                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			//$result = $mailer->send($message);
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
                    }
		}
	}


	/**
	 * Builds a table with the current queue
	 *
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