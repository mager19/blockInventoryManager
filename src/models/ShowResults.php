<?php 

namespace Agency40Q\Blockinventory\models;

class ShowResults {

    static function createTable($results, $type = 'allContent') {

        if(!empty($results)) {
            echo <<<HTML
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Post Type</th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Post Status</th>
                        <th>Author</th>
            HTML;

            echo $type === 'allContent' ? '<th>Link</th>' : '<th>Blocks</th>';
            echo <<<HTML
                        </tr>
                    </thead>
                    <tbody>
            HTML;
            foreach ($results as $result) {
                echo '<tr>';
                echo '<td>' . $result['post_type'] . '</td>';
                echo '<td>' . $result['ID'] . '</td>';
                echo '<td><strong>' . $result['title'] . '</strong></td>';
                echo '<td>' . $result['post_status'] . '</td>';
                echo '<td>' . $result['post_author'] . '</td>';

                echo $type === 'allContent' ? '<td><a href="' . $result['permalink'] . '" target="_blank">View</a></td>' : '<td>' . $result['blocks'] . '</td>';

                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No results found</p>';
        }
    } 
}