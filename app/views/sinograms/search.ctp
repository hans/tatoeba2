<?php
/*
    Tatoeba Project, free collaborative creation of multilingual corpuses project
    Copyright (C) 2009  Allan SIMON (allan.simon@supinfo.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
echo '<div id="searchResults" >';

    echo '<h3>' . __('Results',true) .'</h3>' ;    
    if(count($glyphs)>0){
        foreach( $glyphs as $glyph ){
             echo '<div class="glyph">' .  $glyph . "</div>\n";
        }
    } else {
       echo '<div id="noGlyphFound" >' .
            __('No result found, try to search with less subglyph.', true) .
           "</div>\n";
    }
echo '</div>';

?>
