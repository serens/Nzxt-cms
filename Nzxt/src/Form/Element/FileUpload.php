<?php
namespace Nzxt\Form\Element;

use Signature\Object\ObjectProviderService;

/**
 * Class ContentSelection
 * @package Nzxt\Form\Element
 */
class FileUpload extends \Signature\Html\Form\Element\Input
{
    /**
     * Sets the input type to 'file'.
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @param string $type
     */
    public function __construct(string $name, string $value = '', array $attributes = [], string $type = 'text')
    {
        parent::__construct($name, $value, $attributes, 'file');

        if (isset($_FILES[$name]) && ($filename = $this->uploadFile())) {
            $this->setValue($filename);
        }
    }

    /**
     * Copies an uploaded file into the upload-directory.
     * @throws \RuntimeException
     * @return string The final filename including the upload-directory.
     */
    protected function uploadFile(): string
    {
        $fileData = $_FILES[$this->getAttribute('name')];

        if ($fileData['tmp_name'] != '') {
            /** @var \Signature\Configuration\ConfigurationService $configuration */
            $configuration = ObjectProviderService::getInstance()->getService('ConfigurationService');
            $uploadDirectory = $configuration->getConfigByPath('Nzxt', 'UploadDirectory');

            if (null === $uploadDirectory) {
                throw new \RuntimeException(
                    'No upload directory specified in Configuration Service. ' .
                    'Please set directory in key "UploadDirectory" of module "Nzxt".'
                );
            }

            if (!is_writeable($uploadDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" is not writeable.', $uploadDirectory));
            }

            $filename = $uploadDirectory . DIRECTORY_SEPARATOR . $this->sanitizeFilename($fileData['name']);

            // If input has a value, we assume that the file will be overwritten. Otherwise, create a unique filename:
            if ('' === $this->getValue()) {
                $filename = $this->createUniqeFilename($filename);
            }

            if (move_uploaded_file($fileData['tmp_name'], $filename)) {
                return $filename;
            }
        }

        return '';
    }

    /**
     * Cleans a given filename by replacing or removing special characters.
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        setlocale(LC_ALL, 'en_US.UTF8');

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
        $clean = preg_replace("/[^a-zA-Z0-9_|+ -.]/", '', $clean);
        $clean = trim($clean, '-');
        $clean = preg_replace("/[_|+ -]+/", '-', $clean);

        return $clean;
    }

    /**
     * Checks if the given filename is unique. Otherwise the filename will get modified until the filename is unique.
     *
     * The given filename must include the full path.
     * @param string $filename
     * @return string
     */
    protected function createUniqeFilename(string $filename): string
    {
        $counter   = 1;
        $checkFile = $filename;

        while (file_exists($checkFile)) {
            $checkFile = (false !== ($pos = strrpos($filename, '.')))
                ? substr($filename, 0, $pos) . '-' . $counter . '.' . (substr($filename, $pos + 1))
                : $filename . ('-' . $counter);

            $counter++;
        }

        return $checkFile;
    }

    /**
     * Add some markup to style the file-upload-input field.
     * @return string|void
     */
    public function render(): string
    {
        $elementID = 'file-upload-input-' . $this->getAttribute('id');
        $userInput = '
            <div class="user-input file-upload-input" id="' . $elementID . '">' .
                parent::render() . '
                <div class="value">' . ($this->getValue() ? $this->getValue() : '&nbsp;') . '</div>
                <a class="select-file" title="Click to select a file from your Desktop." href="#"><i class="fa fa-upload"></i></a>
            </div>
        ';

        $javaScript = '
            <script>
                $(function() {
                    var $container = $("#' . $elementID . '");
                    var $input = $("input[type=file]", $container);
                    var $valueDisplay = $(".value", $container);

                    $input.on("change", function() {
                        var selectedFile = $(this).val();

                        // Get rid of "fakepath"-part
                        if (selectedFile && selectedFile.indexOf("\\\") > -1) {
                            var parts = selectedFile.split("\\\");
                            selectedFile = parts.pop();
                        }

                        $valueDisplay.text(selectedFile);
                    });

                    $(".select-file", $container).click(function(e) {
                        e.preventDefault();

                        $input.trigger("click");
                    });
                });
            </script>
        ';

        return $userInput . $javaScript;
    }
}
