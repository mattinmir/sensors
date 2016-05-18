# sensors

### Basic Git

`cd` to directory you want the repository to be in  
`git clone http://www.github.com/mattinmir/sensors.git` to copy the online repository to your system

`git add <file1> [<file2>,...]` to add a file to the list of tracked files. Alternatively use `git add *` to add everything  
`git commit -m"<commit message>"` to commit current state of tracked files
`git push` to push changes to github


### Tips on branches

`git branch <branch>` to create new branch  
`git checkout <branch>` to switch to different branch

To merge changes from \<branch\> into master, checkout to master branch and run `git merge <branch>` 

`git branch` to see all local branches  
`git branch -d <branch>` to remove local branch

`git branch -a` to see all branches including remote ones  
`git remote prune origin` to remove stale remote branches
