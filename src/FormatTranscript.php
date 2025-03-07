<?php

namespace App;

class FormatTranscript
{
    public static function formatVTT(string $data)
    {
        $lines = explode("\n", $data);
        $transcript = '<div id="transcript_container" class="transcript">'; // Parent container
        $buffer = '';
        $time_start = '';

        foreach ($lines as $line) {
            $line = trim($line);

            // Match timestamps
            if (preg_match('/(\d{2}:\d{2}:\d{2}\.\d{3}) --> (\d{2}:\d{2}:\d{2}\.\d{3})/', $line, $matches)) {
                if (!empty($buffer) && !empty($time_start)) {
                    $transcript .= '<div id="' . htmlspecialchars($time_start) . '" class="transcript_line">' . htmlspecialchars($buffer) . '</div>';
                }
                $time_start = $matches[1]; // Capture the start time
                $buffer = ''; // Reset buffer for the next subtitle
            } elseif (!empty($line) && !is_numeric($line)) {
                $buffer .= ' ' . $line; // Append subtitle content
            }
        }

        // Add last subtitle block
        if (!empty($buffer) && !empty($time_start)) {
            $transcript .= '<div id="' . htmlspecialchars($time_start) . '" class="transcript_line">' . htmlspecialchars($buffer) . '</div>';
        }

        $transcript .= '</div>'; // Close parent container
        return $transcript;
    }
}