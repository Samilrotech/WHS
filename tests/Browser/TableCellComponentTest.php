<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TableCellComponentTest extends DuskTestCase
{
    /**
     * Test table-cell component renders with text type
     *
     * @return void
     */
    public function test_table_cell_renders_text_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertSee('Employee');
        });
    }

    /**
     * Test table-cell component renders with badge type
     *
     * @return void
     */
    public function test_table_cell_renders_badge_type()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.badge')
                    ->assertPresent('.whs-table-cell .badge');
        });
    }

    /**
     * Test table-cell component has proper ARIA labels
     *
     * @return void
     */
    public function test_table_cell_has_aria_labels()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertAttribute('.whs-table-cell', 'aria-label', function ($value) {
                        return !empty($value);
                    });
        });
    }

    /**
     * Test table-cell component supports sortable columns
     *
     * @return void
     */
    public function test_table_cell_supports_sortable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('[data-sortable="true"]')
                    ->assertPresent('[data-sortable="true"]');
        });
    }

    /**
     * Test table-cell numeric type has proper alignment
     *
     * @return void
     */
    public function test_table_cell_numeric_alignment()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertPresent('.text-end'); // Right aligned numeric cells
        });
    }

    /**
     * Test table-cell date type renders with time element
     *
     * @return void
     */
    public function test_table_cell_date_renders_time_element()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertPresent('.whs-table-cell time[datetime]');
        });
    }

    /**
     * Test table-cell actions type renders action buttons
     *
     * @return void
     */
    public function test_table_cell_actions_renders_buttons()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertPresent('.actions-cell [role="group"]');
        });
    }

    /**
     * Test table-cell has keyboard focus styles (WCAG 2.1 AA)
     *
     * @return void
     */
    public function test_table_cell_has_focus_styles()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->keys('.whs-table-cell', '{tab}')
                    ->assertPresent('.whs-table-cell:focus-within');
        });
    }

    /**
     * Test table-cell responsive adjustments on mobile
     *
     * @return void
     */
    public function test_table_cell_responsive_mobile()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667) // iPhone SE dimensions
                    ->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertPresent('.whs-table-cell');
        });
    }

    /**
     * Test table-cell meta information displays correctly
     *
     * @return void
     */
    public function test_table_cell_meta_information()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('.whs-table-cell')
                    ->assertPresent('.cell-meta');
        });
    }
}
