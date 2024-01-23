<?php

namespace App\Misc;

class ErrorMessages
{
    const BASIC = 'Something went wrong. Please try again later.';

    const PAYMENTS_ERROR = 'There is something wrong with your subscription. Please check your card.';

    const FETCH_FROM_SITE_FAILED = 'The operation you tried to perform couldn\'t be completed. It\'s possible that Amazon is currently throttling the request. Please try again later';

    const EXISTING_ACC = 'Oops! It looks like you already have an existing account.';

    const ASIN_EXISTS = 'You already keep track of this product.';

    const NO_USER_ASIN = 'You don\'t have a product with this ASIN.';

    const ASIN_NOT_FOUND = 'We couldn\'t find any information about this product. May be try again later.';

    const ASIN_KEYWORDS_NOT_FOUND = 'We couldn\t find any information about this phrase.';

    const ASIN_LIMIT = 'You\'re not allowed to save more products.';

    const ASIN_KEYWORDS_LIMIT = 'You\'re not allowed to save more keywords.';

    const LIMIT_EXCEEDED = 'You\'re not allowed to save more records.';

    const SAME_CATEGORY = 'This is the same category.';

    const NO_PRODUCT_DATA = 'We couldn\'t find any data for this product.';

    const MISSING_FILE = 'No file uploaded';

    const API_TOKEN = 'Missing API token';

    const INVALID_API_CREDENTIALS = 'Your credentials are not valid.';

    const MISSING_ASINS = 'No ASIN found.';
}
