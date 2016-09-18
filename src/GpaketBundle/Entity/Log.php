<?php

namespace GpaketBundle\Entity;

/**
 * Log
 */
class Log
{
    /**
     * @var integer
     */
    private $update_id;

    /**
     * @var string
     */
    private $raw;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \GpaketBundle\Entity\Message
     */
    private $message;


    /**
     * Set updateId
     *
     * @param integer $updateId
     *
     * @return Log
     */
    public function setUpdateId($updateId)
    {
        $this->update_id = $updateId;

        return $this;
    }


	public function getMsg()
	{
		$data = json_decode($this->raw, true);
		$user = '';
		$msg = '';
		$time = '';
		if (isset($data['message']))
			$time = date('d m Y H:m:i', $data['message']['date']);
		if (isset($data['message']['from']['username']))
			$user = $data['message']['from']['username'];
		if (isset($data['message']['text']))
			$msg = $data['message']['text'];
		return "[$time] {$user}: $msg";
	}

    /**
     * Get updateId
     *
     * @return integer
     */
    public function getUpdateId()
    {
        return $this->update_id;
    }

    /**
     * Set raw
     *
     * @param string $raw
     *
     * @return Log
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Get raw
     *
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Log
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set message
     *
     * @param \GpaketBundle\Entity\Message $message
     *
     * @return Log
     */
    public function setMessage(\GpaketBundle\Entity\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \GpaketBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
