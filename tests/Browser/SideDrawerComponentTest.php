<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SideDrawerComponentTest extends DuskTestCase
{
    /**
     * Test side-drawer opens when trigger is clicked
     *
     * @return void
     */
    public function test_side_drawer_opens_on_trigger_click()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertVisible('#employeeDetails');
        });
    }

    /**
     * Test side-drawer shows backdrop when open
     *
     * @return void
     */
    public function test_side_drawer_shows_backdrop()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails-backdrop.show')
                    ->assertVisible('#employeeDetails-backdrop');
        });
    }

    /**
     * Test side-drawer closes when close button is clicked
     *
     * @return void
     */
    public function test_side_drawer_closes_on_close_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->click('[data-drawer-close="employeeDetails"]')
                    ->waitUntilMissing('#employeeDetails.show')
                    ->assertNotVisible('#employeeDetails');
        });
    }

    /**
     * Test side-drawer closes when backdrop is clicked
     *
     * @return void
     */
    public function test_side_drawer_closes_on_backdrop_click()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->click('#employeeDetails-backdrop')
                    ->waitUntilMissing('#employeeDetails.show')
                    ->assertNotVisible('#employeeDetails');
        });
    }

    /**
     * Test side-drawer closes when ESC key is pressed (WCAG 2.1 AA)
     *
     * @return void
     */
    public function test_side_drawer_closes_on_escape_key()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->keys('#employeeDetails', '{escape}')
                    ->waitUntilMissing('#employeeDetails.show')
                    ->assertNotVisible('#employeeDetails');
        });
    }

    /**
     * Test side-drawer has proper ARIA attributes
     *
     * @return void
     */
    public function test_side_drawer_has_aria_attributes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('#employeeDetails')
                    ->assertAttribute('#employeeDetails', 'role', 'dialog')
                    ->assertAttribute('#employeeDetails', 'aria-modal', 'true')
                    ->assertAttribute('#employeeDetails', 'aria-labelledby', 'employeeDetails-title');
        });
    }

    /**
     * Test side-drawer focus trap (Tab navigation)
     *
     * @return void
     */
    public function test_side_drawer_focus_trap()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->keys('#employeeDetails', '{tab}')
                    ->assertFocused('#employeeDetails button, #employeeDetails a, #employeeDetails input');
        });
    }

    /**
     * Test side-drawer restores focus after closing
     *
     * @return void
     */
    public function test_side_drawer_restores_focus()
    {
        $this->browse(function (Browser $browser) {
            $trigger = '[data-drawer-target="employeeDetails"]';

            $browser->visit('/teams')
                    ->click($trigger)
                    ->waitFor('#employeeDetails.show')
                    ->keys('#employeeDetails', '{escape}')
                    ->waitUntilMissing('#employeeDetails.show')
                    ->assertFocused($trigger);
        });
    }

    /**
     * Test side-drawer prevents body scroll when open
     *
     * @return void
     */
    public function test_side_drawer_prevents_body_scroll()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertScript('document.body.style.overflow === "hidden"');
        });
    }

    /**
     * Test side-drawer restores body scroll after closing
     *
     * @return void
     */
    public function test_side_drawer_restores_body_scroll()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->click('[data-drawer-close="employeeDetails"]')
                    ->waitUntilMissing('#employeeDetails.show')
                    ->pause(400) // Wait for animation
                    ->assertScript('document.body.style.overflow === "" || document.body.style.overflow === "visible"');
        });
    }

    /**
     * Test side-drawer different sizes (sm, md, lg, xl)
     *
     * @return void
     */
    public function test_side_drawer_sizes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('#employeeDetails')
                    ->assertHasClass('#employeeDetails', 'drawer-md'); // Default size
        });
    }

    /**
     * Test side-drawer position (left/right)
     *
     * @return void
     */
    public function test_side_drawer_position()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->waitFor('#employeeDetails')
                    ->assertHasClass('#employeeDetails', 'drawer-right'); // Default position
        });
    }

    /**
     * Test side-drawer animation smooth (CSS transitions)
     *
     * @return void
     */
    public function test_side_drawer_animation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->pause(100) // Animation in progress
                    ->assertPresent('#employeeDetails.show');
        });
    }

    /**
     * Test side-drawer header displays title
     *
     * @return void
     */
    public function test_side_drawer_header_title()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertPresent('#employeeDetails-title')
                    ->assertSeeIn('#employeeDetails-title', 'Details');
        });
    }

    /**
     * Test side-drawer footer optional slot
     *
     * @return void
     */
    public function test_side_drawer_footer_optional()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertPresent('.drawer-footer'); // Should exist if slot is used
        });
    }

    /**
     * Test side-drawer responsive on mobile
     *
     * @return void
     */
    public function test_side_drawer_responsive_mobile()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667) // iPhone SE dimensions
                    ->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertVisible('#employeeDetails');
        });
    }

    /**
     * Test side-drawer full width on small screens
     *
     * @return void
     */
    public function test_side_drawer_full_width_on_small_screens()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                    ->visit('/teams')
                    ->click('[data-drawer-target="employeeDetails"]')
                    ->waitFor('#employeeDetails.show')
                    ->assertScript('window.getComputedStyle(document.getElementById("employeeDetails")).width === "100%"');
        });
    }
}
