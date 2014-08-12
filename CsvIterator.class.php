<?php

/**
 * Iterator to easily handle CSV rows
 *
 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
 */
class CsvIterator implements Iterator {
	/**
	 * Represents the current line in $this->handle
	 *
	 * @var int
	 */
	private $position = 0;

	/**
	 * Defines the maximal length of a line
	 *
	 * @var int
	 */
	private $length = null;

	/**
	 * Holds the file pointer/handler
	 *
	 * @var resource
	 */
	private $handle = null;

	/**
	 * Holds the current row
	 *
	 * @var array
	 */
	private $current = null;

	/**
	 * Defines if the rows should be fetched associative
	 *
	 * @var bool
	 */
	private $assoc = null;

	/**
	 * Holds the array keys if $this->assoc is true
	 *
	 * @var array
	 */
	private $keys = null;

	/**#@+
	 * @var string
	 */
	/**
	 * Defines the CSV field enclosure character
	 */
	private $enclosure = null;

	/**
	 * Defines the CSV delimiter
	 */
	private $delimiter = null;

	/**
	 * Defines the CSV escape character
	 */
	private $escape = null;
	/**#@-*/

	/**
	 * Constructor of CsvIterator
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @throws      RuntimeException
	 * @param       string $file
	 * @param       string $delimiter
	 * @param       bool   $assoc
	 * @return      void
	 */
	public function __construct($file, $delimiter = ';', $assoc = false){
		$this->position = 0;
		$this->delimiter = $delimiter;
		$this->enclosure = '"';
		$this->length = 2048; // null == unlimited but slower
		$this->escape = '\\';
		$this->assoc = $assoc;

		$this->handle = @fopen($file, 'r');

		if($this->handle === false){
			throw new RuntimeException(sprintf('Could not open file %s', $file));
		}
	}

	/**
	 * If set to true, the first row will be used as fieldnames
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @param       bool $assoc
	 * @return      this
	 */
	public function setAssoc($assoc){
		$this->assoc = $assoc;

		return $this;
	}

	/**
	 * Sets the escape character used in fgetcsv
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @param       string $escape
	 * @return      this
	 */
	public function setEscape($escape){
		$this->escape = $escape;

		return $this;
	}

	/**
	 * Sets the length used in fgetcsv
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @param       int $length
	 * @return      this
	 */
	public function setLength($length){
		$this->length = $length;

		return $this;
	}

	/**
	 * Sets the enclosing character used in fgetcsv
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @param       string $enclosure
	 * @return      this
	 */
	public function setEnclosure($enclosure){
		$this->enclosure = $enclosure;

		return $this;
	}

	/**
	 * Sets the delimiter character used in fgetcsv
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @param       string $delimiter
	 * @return      this
	 */
	public function setDelimiter($delimiter){
		$this->delimiter = $delimiter;

		return $this;
	}

	/**
	 * Rewinds back to the first element of the Iterator
	 * This method is only called while starting a foreach loop
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      void
	 */
	public function rewind(){
		$this->position = 0;
		rewind($this->handle);

		if($this->assoc){
			$this->keys = fgetcsv($this->handle, $this->length, $this->delimiter);
		}

		$this->current = fgetcsv($this->handle, $this->length, $this->delimiter);
	}

	/**
	 * Returns the current row
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      array
	 */
	public function current(){
		return $this->assoc
			? array_combine($this->keys, $this->current)
			: $this->current;
	}

	/**
	 * Returns the current position (file line)
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      int
	 */
	public function key(){
		return $this->position;
	}

	/**
	 * Moves the current position to the next element
	 * This method is called after each foreach loop
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      void
	 */
	public function next(){
		if(is_resource($this->handle)){
			$this->current = fgetcsv($this->handle, $this->length, $this->delimiter);
			$this->position++;
		}
	}

	/**
	 * This method is called after self::rewind() and self::next() to check if
	 * the current position is valid.
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      bool
	 */
	public function valid(){
		if($this->current === false){
			fclose($this->handle);
			return false;
		}
		return true;
	}

	/**
	 * CsvIterator destructor closes the opened CSV file
	 *
	 * @author      Damian Senn <damian.senn@adfinis-sygroup.ch>
	 * @return      void
	 */
	public function __destruct(){
		if(is_resource($this->handle)) fclose($this->handle);
	}
}
