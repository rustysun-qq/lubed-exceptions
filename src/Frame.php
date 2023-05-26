<?php
namespace Lubed\Exceptions;

class Frame {
    protected $frame;
    protected $file_cache;
    protected $comments = [];

    public function __construct(array $frame) {
        $this->frame = $frame;
    }

    public function getFile() {
        if (empty($this->frame['file'])) {
            return NULL;
        }

        $file = $this->frame['file'];

        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d|assert) code$/', $file, $matches)) {
            $file = $this->frame['file'] = $matches[1];
            $this->frame['line'] = (int) $matches[2];
        }

        return $file;
    }

    public function getLine() {
        return $this->frame['line'] ?? NULL;
    }

    public function getClass() {
        return $this->frame['class'] ?? NULL;
    }

    public function getFunction() {
        return $this->frame['function'] ?? NULL;
    }

    public function getArgs() {
        return isset($this->frame['args']) ? (array) $this->frame['args'] : [];
    }

    public function getFileCache() {
        $file= $this->getFile();
        if (NULL === $this->file_cache && $file) {
            if ("Unknown" === $file) {
                return NULL;
            }

            if (!is_file($filePath)) {
                return NULL;
            }

            $this->file_cache = file_get_contents($file);
        }

        return $this->file_cache;
    }

    public function addComment($comment, $context = 'global') {
        $this->comments[] = [
            'comment' => $comment,
            'context' => $context,
        ];
    }

    public function getComments($filter = NULL) {
        $comments = $this->comments;

        if ($filter !== NULL) {
            $comments = array_filter($comments, function ($c) use ($filter) {
                return $c['context'] == $filter;
            });
        }

        return $comments;
    }

    public function getRawFrame() {
        return $this->frame;
    }

    public function getFileLines(int $start = 0, int $length = 0) {
        $file_content = $this->fileCache();

        if (!$file_content) {
            return NULL;
        }

        $lines = explode("\n", $file_content);

        if ($length) {
            if ($start < 0) {
                $start = 0;
            }

            $lines = array_slice($lines, $start, $length, TRUE);
        }

        return $lines;
    }

    public function serialize() {
        $frame = $this->frame;
        if (!empty($this->comments)) {
            $frame['_comments'] = $this->comments;
        }

        return serialize($frame);
    }

    public function unserialize($serializedFrame) {
        $frame = unserialize($serializedFrame);

        if (!empty($frame['_comments'])) {
            $this->comments = $frame['_comments'];
            unset($frame['_comments']);
        }

        $this->frame = $frame;
    }

    public function equals(Frame $frame) {
        if (!$this->getFile() || $this->getFile() === 'Unknown' || !$this->getLine()) {
            return FALSE;
        }

        return $frame->getFile() === $this->getFile() && $frame->getLine() === $this->getLine();
    }
}
