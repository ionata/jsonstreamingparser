<?php
namespace JsonStreamingParser\Listener;

use JsonStreamingParser\Listener;

/**
 * This basic geojson implementation of a listener simply constructs an in-memory
 * representation of the JSON document at the second level, this is useful so only
 * a single Feature will be kept in memory rather than the whole FeatureCollection.
 */
class GeoJsonListener implements Listener
{
    private $json;

    private $stack;
    private $key;

    // Level is required so we know how nested we are.
    private $level;

    public function getJson()
    {
        return $this->json;
    }

    public function startDocument()
    {
        $this->stack = array();
        $this->level = 0;
        // Key is an array so that we can can remember keys per level to avoid
        // it being reset when processing child keys.
        $this->key = array();
    }

    public function endDocument()
    {
        // w00t!
    }

    public function startObject()
    {
        $this->level++;
        $this->stack[] = array();
        // Reset the stack when entering the second level
        if ($this->level == 2) {
            $this->stack = array();
            $this->key[$this->level] = null;
        }
    }

    public function endObject()
    {
        $this->level--;
        $obj = array_pop($this->stack);
        if (empty($this->stack)) {
            // doc is DONE!
            $this->json = $obj;
        } else {
            $this->value($obj);
        }
        // Output the stack when returning to the second level
        if ($this->level == 2) {
            var_dump($this->json);
        }
    }

    public function startArray()
    {
        $this->startObject();
    }

    public function endArray()
    {
        $this->endObject();
    }

    /**
     * @param string $key
     */
    public function key($key)
    {
        $this->key[$this->level] = $key;
    }

    /**
     * Value may be a string, integer, boolean, null
     * @param mixed $value
     */
    public function value($value)
    {
        $obj = array_pop($this->stack);
        if ($this->key[$this->level]) {
            $obj[$this->key[$this->level]] = $value;
            $this->key[$this->level] = null;
        } else {
            $obj[] = $value;
        }
        $this->stack[] = $obj;
    }

    public function whitespace($whitespace)
    {
        // do nothing
    }
}
