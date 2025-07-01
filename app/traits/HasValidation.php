<?php

namespace app\traits;

use support\Response;

trait HasValidation
{
    /**
     * Validates required fields from request data.
     *
     * @param array $data Request data
     * @param array $required_fields Array of required field names
     * @return Response|null Returns error response if validation fails, null if passes
     */
    protected function validateRequiredFields(array $data, array $required_fields): ?Response
    {
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            return api_response(
                false, 
                'Missing required fields: ' . implode(', ', $missing_fields), 
                null, 
                400
            );
        }
        
        return null;
    }

    /**
     * Validates pagination parameters.
     *
     * @param int $per_page
     * @param int $max_limit
     * @return int Sanitized per_page value
     */
    protected function validatePagination(int $per_page, int $max_limit = 100): int
    {
        return min(max($per_page, 1), $max_limit);
    }
}