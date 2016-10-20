<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 20-10-2016
 * Time: 11:47
 */

namespace RoomManager\Core\Utility;


class BuildRules
{
    private $structure;

    private $append;

    private $done;

    public function __construct(array $structure, $append = true)
    {
        $this->structure = $structure;
        $this->append = $append;
    }

    public function build(array &$json) {
        $this->done = [];

        $data = $this->_build($json, $this->structure);

        if ($this->append) {
            foreach ($json as $name => $value) {
                if (!in_array($name, $this->done)) {
                    $data[$name] = $value;
                }
            }
        }

        $json = $data;
    }

    private function _build(array &$json, array $structure) {
        $data = [];
        foreach ($structure as $name => $value) {
            if (is_array($value)) {
                $data[$name] = $this->_build($json, $structure[$name]);
            } else {
                $data[$name] = $json[$value];

                if ($this->append) {
                    $this->done[] = $value;
                }
            }
        }
        return $data;
    }
}