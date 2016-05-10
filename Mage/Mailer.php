<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage;

/**
 * Mailer Helper.
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 * @author César Suárez <suarez.ortega.cesar@gmail.com>
 */
class Mailer
{
    const EOL = "\r\n";
    const SUBJECT = '[Magallanes] Deployment of {project} to {environment}: {result}';

    protected $address;
    protected $project;
    protected $environment;
    protected $logFile;
    protected $cc;
    protected $bcc;

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
        return $this;
    }

    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    public function send($result)
    {
        $boundary = md5(date('r', time()));

        $headers = 'From: ' . $this->address
            . self::EOL
            . 'Reply-To: ' . $this->address
            . self::EOL
            . 'MIME-Version: 1.0'
            . self::EOL
            . 'Content-Type: multipart/mixed; boundary=Mage-mixed-' . $boundary;

        if ($this->cc) {
            $headers .= self::EOL . 'Cc: ' . $this->cc;
        }

        if ($this->bcc) {
            $headers .= self::EOL . 'Bcc: ' . $this->bcc;
        }

        $subject = str_replace(
            array('{project}', '{environment}', '{result}'),
            array($this->project, $this->environment, $result ? 'SUCCESS' : 'FAILURE'),
            self::SUBJECT
        );
        $attachment = chunk_split(base64_encode(file_get_contents($this->logFile)));

        $message = 'This is a multi-part message in MIME format.' . self::EOL
            . '--Mage-mixed-' . $boundary . self::EOL
            . 'Content-Type: text/plain; charset=iso-8859-1' . self::EOL
            . 'Content-Transfer-Encoding: quoted-printable' . self::EOL
            . self::EOL
            . strip_tags(Console::getOutput()) . self::EOL
            . self::EOL
            . '--Mage-mixed-' . $boundary . self::EOL
            . 'Content-Type: text/plain; name="log.txt"' . self::EOL
            . 'Content-Transfer-Encoding: base64' . self::EOL
            . 'Content-Disposition: attachment' . self::EOL
            . self::EOL
            . $attachment . self::EOL
            . '--Mage-mixed-' . $boundary . '--' . self::EOL;

        return mail($this->address, $subject, $message, $headers);
    }
}
