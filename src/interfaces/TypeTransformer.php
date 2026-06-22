<?php

namespace framework\validation\interfaces;

interface TypeTransformer
{
    public function transformFromDatabase($value);
    public function transformToDatabase($value);

    /**
     * Determine if the given raw value should be considered empty/skipped
     * during model loading. For example, an uploaded file input with no
     * file selected should be treated as empty.
     */
    public function isEmpty($value): bool;
}
