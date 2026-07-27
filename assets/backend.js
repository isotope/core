import './styles/backend.scss';

import { Application } from '@hotwired/stimulus';
import { definitionForModuleAndIdentifier, identifierForContextKey } from '@hotwired/stimulus-webpack-helpers';

// Start the Stimulus application
const application = Application.start();
application.debug = process.env.NODE_ENV === 'development';

// Register all controllers with `isotope--` prefix
const context = require.context('./backend-controllers', true, /\.js$/);

application.load(
    context
        .keys()
        .map((key) => {
            const identifier = identifierForContextKey(key);

            return identifier ? definitionForModuleAndIdentifier(context(key), `isotope--${identifier}`) : null;
        })
        .filter((value) => value),
);
