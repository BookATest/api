import SwaggerUI from 'swagger-ui';

SwaggerUI({
    dom_id: '#app',
    url: "/docs/openapi.json",
    defaultModelsExpandDepth: -1,
    docExpansion: 'none'
});
