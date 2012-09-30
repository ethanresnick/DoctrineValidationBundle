Doctrine Entity Validation Bundle
=================================

This bundle automatically validates any Doctrine entity when it's added or changed (i.e. at the prePersist or preUpdate events).
It does this by hooking into Doctrine's event system. It gets the changed entity and then validates it using 
Symfony's Validator component, which in turn works by reading the annotations on your entity class.

So, to use this bundle, simply install it (i.e. add it to AppKernel.php and autoload.php) and then add Symfony Validator annotations
to your entity classes.

While any forms you create to modify/add entities should still do their own validation (mostly so you can catch errors before they get
to doctrine), this bundle acts as a fallback to guarantee that no bad data gets into your database, even if you forget to validate a form.