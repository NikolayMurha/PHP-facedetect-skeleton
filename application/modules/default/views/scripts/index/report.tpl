{foreach $files as $file}
    <table>
    {foreach $files.rows as $row}
        <tr>
            {foreach $row as $cell}
                {if $cell|@is_array}
                    {$cell|@count}
                {else}
                    {$cell}
                {/if}
            {/foreach}
        </tr>
    {/foreach}
    </table>
{/foreach}