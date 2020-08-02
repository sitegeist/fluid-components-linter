# Coding Guidelines for Fluid Components

## Basic Requirements
<f:if condition="{configuration.component.requireStrictComponentStructure.check}">
* All output of a component must happen within its `fc:renderer` tag.
</f:if><f:if condition="{configuration.component.requireFixtureFile.check}">
* Each component should be visible in the styleguide and thus needs a **fixture file**.
</f:if><f:if condition="{configuration.component.requireDocumentation.check}">
* Each component needs a **markdown documentation file**.
</f:if><f:if condition="{configuration.component.requireDocumentationWithFixtureFile.check} && !{configuration.component.requireDocumentation.check}">
* If a component is visible in the living styleguide, it needs a **documentation file** written in markdown.
</f:if>

## Component Parameters

* Components can have between **{configuration.param.count.min}** and **{configuration.param.count.max}** parameters to keep them as simple as possible.
