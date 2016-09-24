<?php

namespace GpaketBundle\Entity;

/**
 * Dictionary
 */
class Dictionary
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $keyword;
    private $preg_ready;

    /**
     * @var array
     */
    private $answers;


	public function getPregKeyword() {
		if (is_null($this->preg_ready))
			$this->preg_ready = preg_quote($this->keyword);
		return $this->preg_ready;
	}

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyword
     *
     * @param string $keyword
     *
     * @return Dictionary
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set answers
     *
     * @param array $answers
     *
     * @return Dictionary
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get answers
     *
     * @return array
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}
