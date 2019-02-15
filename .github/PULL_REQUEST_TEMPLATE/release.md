## Summary

[This release adds these feature...]

## Guidelines

Please ensure your pull request adheres to the following guidelines:

- [ ] Branch is based off of `develop`
- [ ] Pull requests will be create to merge into `master` and `develop`
- [ ] Merge strategy set to `squash and merge`
- [ ] The branch will be deleted after merging

Thanks for contributing!

## Guidance

A release branch is based off of `develop` and is intended to contain all
the new features completed in `develop` at the time of release.

This branch is then intended to be released on the staging environment for
quality assurance before deploying to production.

Any issues or changes that are identified during quality assurance should
be worked on, directly within the release branch.

Once work is completed, two pull requests should be created to merge the
branch into both `master` and `develop` with the merge strategy set to 
`sqush and merge`.

The release branch should then be deleted from the remote.
