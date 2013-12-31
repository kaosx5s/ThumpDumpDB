Dumps the entire list of armies in Firefall, including player id numbers, ranks and anything else related to an army.
Warning: This thing takes ages to finish because of callback spam limits, prepare to wait 1h+ for it to finish. I was supposed to optimize this 
but never got around to it.

The army roster is dumped first (list of all armies that exist in Firefall), following that it will iterate through each and every army. 
Due to the size of the data we would send it in blocks of 500.