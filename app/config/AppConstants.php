<?php

namespace app\config;

class AppConstants
{
    // Pagination constants
    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 100;
    public const MIN_PER_PAGE = 1;

    // JWT constants
    public const DEFAULT_JWT_TTL = 3600; // 1 hour

    // Content constants
    public const DEFAULT_CONTENT_TYPE = 'post';
    public const DEFAULT_CONTENT_STATUS = 'draft';

    // Cache constants
    public const OPTIONS_CACHE_TTL = 86400; // 24 hours
    public const OPTIONS_CACHE_KEY = 'sword_options';

    // Validation constants
    public const MAX_SLUG_SUFFIX_LENGTH = 32;
    public const UUID_LENGTH = 16;

    // Content types
    public const CONTENT_TYPE_POST = 'post';
    public const CONTENT_TYPE_AUDIO_SAMPLE = 'audio_sample';

    // Content statuses
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT = 'draft';

    // User roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
}