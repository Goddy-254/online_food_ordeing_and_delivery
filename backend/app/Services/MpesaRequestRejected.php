<?php

namespace App\Services;

use RuntimeException;

/** Indicates Daraja explicitly rejected an STK request before accepting it. */
class MpesaRequestRejected extends RuntimeException
{
}
