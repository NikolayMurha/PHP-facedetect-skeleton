{if $message}
{$message}
{elseif $exception}
{$exception->getMessage()}
{/if}

