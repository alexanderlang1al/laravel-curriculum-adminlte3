describe('logbook tests (teacher role)', () => {
    const username = 'teacher';
    const password = 'password';

    const title = 'UI-Test Logbook';
    const description = 'UI-Test Logbook - Description';
    let modelID = 0;

    context('CRUD', () => {
        //Authenticate Teacher
        beforeEach('signs into teacher', () => {
            cy.visit('/login');
            cy.get('input[name="email"]')
                .type(username)
                .should('have.value', username)

                .get('input[name="password"]')
                .type(password)
                .should('have.value', password)

                .get('button[type="submit"]')
                .click();
        });

        it('adds new logbook', () => {
            cy.visit('/logbooks')
                .get('#add-logbook')
                .click();

            cy.get('div[class="vue-swatches"]')
                .click()
                .get('div[aria-label="#A463BF"]')
                .click()

                .get('#title')
                .type(title);

            cy.window()
                .then(win => {
                win.tinymce.activeEditor.setContent("<p>" + description + "</p>");
            });

            cy.get('#logbook-save')
                .click();
            
            cy.url().then((url) => {
                const parts = url.split('/');
                modelID = parts.pop() || parts.pop();  // get modelId
            });
        });

        it('adds new logbook entry1', () => {
            cy.visit('logbooks/' + modelID)
                .get('#add-logbook-entry')
                .click();

            cy.get('#title')
                .type('Entry One');

            cy.window()
                .then(win => {
                win.tinymce.activeEditor.setContent("<p>This is the description of the first entry</p>");
            });

            cy.contains('Speichern')
                .click();
        });

        it('adds new logbook entry2', () => {
            cy.visit('logbooks/' + modelID)
                .get('#add-logbook-entry')
                .click();

            cy.get('#title')
                .type('Entry Two');

            cy.window()
                .then(win => {
                win.tinymce.activeEditor.setContent("<p>This is the description of the second entry</p>");
            });

            cy.contains('Speichern')
                .click();
        });

        it('edits logbook entry2', () => {
            cy.visit('logbooks/' + modelID)
                .get('input[type="search"]')
                .type('two');
        })
    })
})