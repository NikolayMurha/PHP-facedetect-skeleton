{foreach $files as $file}
    <table>
    {foreach $file.rows as $row}
        <tr>
            {foreach $row as $cell}
                <td>
                    {if $cell|@is_array}
                        {$cell|@count}
                    {else}
                        {$cell}
                    {/if}
                </td>
            {/foreach}
        </tr>
    {/foreach}
    </table>
{/foreach}