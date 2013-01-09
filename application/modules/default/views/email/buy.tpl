Dear {if !$user.first_name && !$user.last_name}{$user.email}{else}{$user.first_name} {$user.last_name}{/if},<br>
this is to confirm you just purchased the song from Tunehog Discovery. You can download it via the following link:<br>
<a href="{$track.url}">{$track.url}</a><br>
<br>
Regards,<br>
Tunehog Discovery team
