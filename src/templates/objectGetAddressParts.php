
/**
 * Retrieve the address parts to be geocoded.
 *
 * You can extend this method to fill in other fields.
 *
 * @return array
 */
public function getAddressParts()
{
    $parts    = array();
<?php foreach ($columns as $constantName => $phpName) : ?>
    // <?php echo $phpName . "\n" ?>
    $parts['<?php echo strtolower($phpName) ?>'] = $this->get<?php echo ucfirst($phpName) ?>();

<?php endforeach; ?>

    return $parts;
}
