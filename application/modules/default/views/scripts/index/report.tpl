{foreach $files as $file}

    <table style="background: #fff">
        <tr>
            <th colspan="20" style="text-align: left">
                {$file.file}<br>
                <img src="/images/random/{$file.file}" width="200" />
            </th>
        </tr>
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