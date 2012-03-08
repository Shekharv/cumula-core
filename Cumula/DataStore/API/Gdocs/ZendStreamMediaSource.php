<?php
namespace GDocsAPIDataStore;


class GDocsZendStreamMediaSource extends \Zend_Gdata_App_BaseMediaSource
{

    /**
     * The content type for the file attached (example image/png)
     *
     * @var string
     */
    protected $_contentType = null;

	protected $_content = null;

    /**
     * Create a new Zend_Gdata_App_MediaFileSource object.
     *
     * @param string $filename The name of the file to read from.
     */
    public function __construct($content)
    {
        $this->setContent($content);
    }

    /**
     * Return the MIME multipart representation of this MediaEntry.
     *
     * @return string
     * @throws Zend_Gdata_App_IOException
     */
    public function encode()
    {
        return $this->_content;
	}

		public function setContent($content) {
			$this->_content = $content;
			return $this;
		}
		
		public function getContent() {
			return $this->_content;
		}

    /**
     * The content type for the file attached (example image/png)
     *
     * @return string The content type
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * Set the content type for the file attached (example image/png)
     *
     * @param string $value The content type
     * @return Zend_Gdata_App_MediaFileSource Provides a fluent interface
     */
    public function setContentType($value)
    {
        $this->_contentType = $value;
        return $this;
    }

    /**
     * Alias for getFilename().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_content;
    }

}
