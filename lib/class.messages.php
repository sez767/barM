<?php
/**
 * Messages
 */
class Messages {
    
    public function send() {
        
    }

    /**
     * Log
     * @param  string $txt Log text
     */
    public function l($txt = "") {
        error_log(
            $txt . "\n",
            3,
            dirname(__FILE__) . "/../logs/messages_" . date('Ymd') . ".log"
        );
    }
}