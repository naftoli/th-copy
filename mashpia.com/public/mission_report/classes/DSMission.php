<?php
require_once 'missionDisplay.php';

class DSMission extends MissionDisplay {

    public function __construct( $mission ) {
        parent::__construct( $mission );
    }

    public function pager( $page, $totalRendered, $totalRows, $addLabel = 0, $totalDaily = 0, $rendered = 0 ) {
        /**
         * figure out when to make second column and when to make new page
         * returns 1 to columnize and 2 to pagify (0 to do nothing)
         **/

        $columnizeFirst = 12;
        $newPageFirst = 23;
        $columnizeReg = 12;
        $newPageReg = 24;
        $columnizeLast = 12;
        $newPageLast = 24;

        // added to fix overflow issue from footer when there was 49 total tasks (tasks + labels/2)
        if ($totalRows == 49) {
            $newPageReg = 25; // added to fix overflow issue from footer when there was 49 total tasks (tasks + labels/2)
        }

        $lastPage = 1;
        if ($totalRows > $newPageFirst) {
            $lastPage++;
            if ($totalRows > ($newPageFirst + $newPageReg)) {
                $lastPage++;
                if ($totalRows > ($newPageFirst + ($newPageReg * 2))) {
                    $lastPage++;
                    if ($totalRows > ($newPageFirst + ($newPageReg * 3))) {
                        $lastPage++;
                        if ($totalRows > ($newPageFirst + ($newPageReg * 4))) {
                            $lastPage++;
                            if ($totalRows > ($newPageFirst + ($newPageReg * 5))) {
                                $lastPage++;
                                if ($totalRows > ($newPageFirst + ($newPageReg * 6))) {
                                    $lastPage++;
                                    if ($totalRows > ($newPageFirst + ($newPageReg * 7))) {
                                        $lastPage++;
                                        if ($totalRows > ($newPageFirst + ($newPageReg * 8))) {
                                            $lastPage++;
                                            if ($totalRows > ($newPageFirst + ($newPageReg * 9))) {
                                                $lastPage++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($page == 1) {
            if (
                ($totalRendered == $columnizeFirst) ||
                ($totalRendered < $columnizeFirst && (($totalRendered + $addLabel) >= $columnizeFirst))
            ) {
                return 1;
            } else if (($totalRendered + $addLabel) >= $newPageFirst) {
                return 2;
            } else {
                return 0;
            }
        } else if ($page == $lastPage) {
            if (
                ($totalRendered == $columnizeLast) ||
                ($totalRendered < $columnizeLast && (($totalRendered + $addLabel) >= $columnizeLast))
            ) {
                return 1;
            } else if (($totalRendered + $addLabel) >= $newPageLast) {
                return 2;
            } else {
                return 0;
            }
        } else {
            if (
                ($totalRendered == $columnizeReg) ||
                ($totalRendered < $columnizeReg && (($totalRendered + $addLabel) >= $columnizeReg))
            ) {
                return 1;
            } else if (($totalRendered + $addLabel) >= $newPageReg) {
                return 2;
            } else {
                return 0;
            }
        }
    }
}
?>