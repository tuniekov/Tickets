<?php

namespace Tickets2\Model;

use MODX\Revolution\Mail\modMail;
use MODX\Revolution\Mail\modPHPMailer;
use MODX\Revolution\modUser;
use xPDO\Om\xPDOSimpleObject;
use xPDO\xPDO;

/**
 * Class TicketQueue
 * @package Tickets2\Model
 * @property string $subject
 * @property string $body
 * @property string $email
 */
class TicketQueue extends xPDOSimpleObject
{
    /**
     * Send email from queue
     * 
     * @return bool|string
     */
    public function Send(): bool|string
    {
        /** @var modPHPMailer $mail */
        $mail = $this->xpdo->getService('mail', modPHPMailer::class);
        $mail->setHTML(true);

        $mail->set(modMail::MAIL_SUBJECT, $this->subject);
        $mail->set(modMail::MAIL_BODY, $this->body);
        $mail->set(modMail::MAIL_SENDER,
            $this->xpdo->getOption('tickets2.mail_from', null, $this->xpdo->getOption('emailsender'), true)
        );
        $mail->set(modMail::MAIL_FROM,
            $this->xpdo->getOption('tickets2.mail_from', null, $this->xpdo->getOption('emailsender'), true)
        );
        $mail->set(modMail::MAIL_FROM_NAME,
            $this->xpdo->getOption('tickets2.mail_from_name', null, $this->xpdo->getOption('site_name'), true)
        );

        /** @var modUser $user */
        if ($user = $this->getOne('User')) {
            $profile = $user->getOne('Profile');
            if (!$user->get('active') || $profile->get('blocked')) {
                return 'This user is not active.';
            }
            $email = $profile->get('email');
        } else {
            $email = $this->get('email');
        }

        if (empty($email)) {
            return 'Could not get email.';
        }

        $mail->address('to', $email);
        if (!$mail->send()) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                'An error occurred while trying to send the email: ' . $mail->mailer->ErrorInfo
            );

            $mail->reset();
            return false;
        }
        
        $mail->reset();
        return true;
    }
} 