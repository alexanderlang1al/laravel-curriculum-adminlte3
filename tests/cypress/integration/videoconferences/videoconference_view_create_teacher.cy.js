describe('videoconference test (teacher role)', () => {
    const username = 'teacher';
    const password = 'password';

    context('CRUD', () => {
        //Authenticate Teacher
        beforeEach(() => {
            cy.visit('/login');
            cy.get('input[name="email"]')
                .type(username)
                .should('have.value', username)

                .get('input[name="password"]')
                .type(password)
                .should('have.value', password)

                .get('button[name="login"]')
                .click();
        });

        // it('add new videoconference with all toggles off', () => {
        //     cy.visit('/videoconferences')
        //         .get('#videoconference-add')
        //         .click()

        //     cy.get('div[class="vue-swatches__trigger__wrapper"]')
        //         .click()
        //         .get('div[aria-label="#E84B3C"]')
        //         .click()

        //         .get('#meetingName')
        //         .type('Alle Toggles deaktiviert')
        //         .should('have.value', 'Alle Toggles deaktiviert');

        //     // get the parent-element of toggles
        //     cy.get('.card-body > form').children().each($el => {
        //         // get the toggle-element
        //         const toggle = $el[0].firstChild.firstChild;
        //         // set toggle to checked
        //         if (toggle.checked) toggle.click();
        //     });

        //     cy.get('.btn-primary').first().click();
        // });

        // it('add new videoconference with all toggles on', () => {
        //     cy.visit('/videoconferences')
        //         .get('#videoconference-add')
        //         .click()

        //     cy.get('div[class="vue-swatches__trigger__wrapper"]')
        //         .click()
        //         .get('div[aria-label="#2ECC70"]')
        //         .click()

        //         .get('#meetingName')
        //         .type('Alle Toggles aktiviert')
        //         .should('have.value', 'Alle Toggles aktiviert');

        //     // get the parent-element of toggles
        //     cy.get('.card-body > form').children().each($el => {
        //         // get the toggle-element
        //         const toggle = $el[0].firstChild.firstChild;
        //         // set toggle to unchecked
        //         if (!toggle.checked) toggle.click();
        //     });

        //     cy.get('.btn-primary').first().click();
        // });

        it('delete all videoconferences', () => {
            cy.visit('/videoconferences');

            cy.get('div[id^="videoconferenceDropdown"]').each($el => {
                cy.wrap($el)
                    .click()
                    .children().last().children().last()
                    .click();
            
                cy.wait(500) // wait for modal to appear
                    .get('.btn-primary').last() // two btn-primary exist, second one is the deletion-modal
                    .click()
                    .wait(1000); // wait for modal to disappear
            });
        });
    });
});