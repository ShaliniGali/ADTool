<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Release_notes_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->releaseNotesPath = APPPATH.'release_notes/RELEASE_NOTES_UI.md';
    }
    public function index()
    {
        /**
         * Show release notes only to Super Admins
         */
        $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin();
        /**
         * If not super admin redirect to home page.
         */
        if (!(auth_coa_role_guest()!= null || auth_coa_role_restricted() != null)) {
            $data['versions'] = $this->get_versions();
            $this->load->view('release_notes_view', $data);
        } else {
            $http_status = 403;
            $response['status'] = "Unauthorized user, access denied.";
            show_error($response['status'], $http_status);
        }
    }

    public function get_note() {
        header(ACCEPT_JSON_STRING);
        $release_notes_file = file_get_contents($this->releaseNotesPath);
        $markdownHTML = $this->markdownToHTML($release_notes_file);
        echo $markdownHTML;
    }

    private function markdownToHTML($markdown)
    {
        // Replace Markdown syntax with HTML tags manually
        $sections = preg_split('/^\*\*# (.*)\*\*$/m', $markdown, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $sectionHeader = '
            <section class="release-note-section d-flex justify-content-center">
                <div>
                    <h1 class="release-note-main">
                        <strong> Rhombus Power - %s </strong>
                    </h1>
                </div>
            </section>
        ';
        $htmlOutput = sprintf($sectionHeader, RHOMBUS_PROJECT_NAME);
        $htmlOutput .= '<div style="width: 22vw; margin-left: auto; margin-right: 4rem; margin-top: 0.5rem">'.
                '<select id="release-notes-select" style="width:10vw;margin-right:5px;">'.
                '</select>'.
            '</div>';
        // Loop through each section and format it
        for ($i = 0; $i < count($sections); $i++) {
            if ($i % 2 == 0) {
                // This is a section header
                $sectionTitle = trim($sections[$i]);

                // Format the section as per the desired format
                $articleHTML = '<article>';
                $articleHTML .= '<h2 id="head-'
                .$i.'" class="d-flex align-items-center release-notes-version">' .
                htmlspecialchars($sectionTitle) . '</h2>';

                // Get the content of this section (next element in the array)
                $sectionContent = trim($sections[$i + 1]);
                $articleHTML .= $this->markdownSectionHtml($sectionContent);
                $articleHTML .= '</article>';
                $htmlOutput .= $articleHTML;
            }
        }

        return $htmlOutput;
    }

    private function markdownSectionHtml($markdown)
    {
        // Markdown bold to html strong
        $markdown = preg_replace(
            '/^### (.*)$/m', '<h3 class="release-notes-subheader"><strong>$1</strong></h3>', $markdown
        );
        // Markdown bold to html strong
        $markdown = preg_replace(
            '/^## (.*)$/m', '<h2 class="release-notes-header"><strong>$1</strong></h2>', $markdown
        );

        $markdown = preg_replace(
            '/^#### (.*)$/m', '<h4 class="release-notes-header"><strong>$1</strong></h4>', $markdown
        );

        $markdown = preg_replace(
            '/^##### (.*)$/m', '<h5 class="release-notes-header"><strong>$1</strong></h5>', $markdown
        );
        // Markdown to html list items
        $markdown = preg_replace('/^- (.+)$/m', '<li class="release-notes-item">$1</li>', $markdown);
        return $markdown;
    }


    public function get_versions() {
        $release_notes_file = file_get_contents($this->releaseNotesPath);

        preg_match_all(
            '/\*\*# SOCOM v([\d\.]+) Release Notes\*\*\s+### Release Date:\s*(\d{2})\/(\d{2})\/(\d{4})/',
            $release_notes_file,
            $matches,
            PREG_SET_ORDER
        );
    
        $versions = [];
    
        foreach ($matches as $match) {
            $version = $match[1];
            $month = $match[2];
            $day = $match[3];
            $year = $match[4];
    
            $versions[] = "Version $version -  $month/$day/$year";
        }

        return $versions;
    }
}
