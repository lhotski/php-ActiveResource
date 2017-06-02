<?php

namespace ActiveResource\Formats;


class Json implements Format {
    /**
     * Returns format extension for resource
     *
     * @return  string  resource format extension
     */
    public function getExtension()
    {
        return 'json';
    }

    /**
     * Returns format MIME type for resource
     *
     * @return  string  request/response MIME type
     */
    public function getMimeType()
    {
        return 'application/json';
    }

    /**
     * Encodes object values to resource attributes
     *
     * @param   array $attrs object values
     *
     * @return  string          request body
     */
    public function encode(array $attrs)
    {
        return json_encode($attrs);
    }

    /**
     * Decodes resource attributes to object values
     *
     * @param   string $body response body
     *
     * @return  array         object values
     */
    public function decode($body)
    {
        return json_decode($body,true);
    }

}