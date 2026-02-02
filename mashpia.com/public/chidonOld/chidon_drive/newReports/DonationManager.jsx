const { useState, useEffect } = React;

function DonationManager() {
    const [donations, setDonations] = useState({});
    const [subsidies, setSubsidies] = useState({});
    const [changes, setChanges] = useState({}); // Track changes by donation ID
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchDonations();
    }, []);

    const fetchDonations = async () => {
        try {
            const response = await fetch('../ajax/getDonations.php');
            const [donations, subsidies] = await response.json();
            setDonations(donations);
            setSubsidies(subsidies);
            setChanges({}); // Reset changes after fetch
            setLoading(false);
        } catch (err) {
            setError('Failed to load donations');
            setLoading(false);
        }
    };

    const validateSubsidyUpdates = (donationId, newSubsidies) => {
        const donation = donations[donationId];
        
        // Calculate total of all subsidies
        const totalSubsidies = newSubsidies.reduce((sum, s) => sum + Number(s.subsidy_amount), 0);
        
        // Return validation result with totals
        return {
            isValid: totalSubsidies === Number(donation.donation_amount),
            totalSubsidies,
            donationAmount: Number(donation.donation_amount)
        };
    };

    const updateSubsidies = async (donationId) => {
        try {
            const updatedSubsidies = subsidies[donationId];
            
            // Get list of changed subsidies
            const changedSubsidies = changes[donationId] || [];
            if (changedSubsidies.length === 0) {
                alert('No changes to update');
                return;
            }

            // Validate total amount
            const validation = validateSubsidyUpdates(donationId, updatedSubsidies);
            if (!validation.isValid) {
                const proceed = window.confirm(
                    `Warning: Total subsidies ($${validation.totalSubsidies}) does not match donation amount ($${validation.donationAmount}). \n\nDo you want to proceed with the update anyway?`
                );
                if (!proceed) return;
            }

            // Prepare all updates
            const updates = changedSubsidies.map(subsidyId => {
                const subsidy = updatedSubsidies.find(s => s.chidon_user_subsidy_id === subsidyId);
                return {
                    subsidyId,
                    amount: subsidy.subsidy_amount
                };
            });

            // Send all updates in one request
            const response = await fetch('ajax/updateSubsidies.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    updates
                }),
            });

            if (!response.ok) throw new Error('Failed to update subsidies');

            await fetchDonations();

            // Clear changes for this donation
            setChanges(prev => {
                const newChanges = { ...prev };
                delete newChanges[donationId];
                return newChanges;
            });

        } catch (err) {
            console.error('Error updating subsidies:', err);
            alert('Failed to update subsidies');
        }
    };

    if (loading) return <div>Loading...</div>;
    if (error) return <div className="alert alert-danger">{error}</div>;

    return (
        <div>
            <style>
                {`
                    .fixed-width-button {
                        width: 300px;
                        white-space: nowrap;
                    }
                    .fixed-width-cell {
                        width: 300px;
                    }
                `}
            </style>
            <h2>Donations</h2>
            <table className="table">
                <thead>
                    <tr>
                        <th>Donation ID</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Subsidies</th>
                    </tr>
                </thead>
                <tbody>
                    {Object.entries(donations).map(([donationId, donation]) => (
                        <tr key={donationId}>
                            <td>{donationId}</td>
                            <td>${donation.donation_amount}</td>
                            <td>{donation.donation_date}</td>
                            <td>
                                <table className="table table-sm">
                                    <thead>
                                        <tr>
                                            <th className="fixed-width-cell">User ID</th>
                                            <th className="fixed-width-cell">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {(subsidies[donationId] || []).map(subsidy => (
                                            <tr key={subsidy.chidon_user_subsidy_id}>
                                                <td className="fixed-width-cell">
                                                    <span className="form-control-plaintext">
                                                        {subsidy.user_id}
                                                    </span>
                                                </td>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        className="form-control form-control-sm"
                                                        value={subsidy.subsidy_amount} 
                                                        step="0.01"
                                                        onChange={(e) => {
                                                            const newAmount = e.target.value;
                                                            setSubsidies(prev => ({
                                                                ...prev,
                                                                [donationId]: prev[donationId].map(s => 
                                                                    s.chidon_user_subsidy_id === subsidy.chidon_user_subsidy_id
                                                                        ? { ...s, subsidy_amount: newAmount }
                                                                        : s
                                                                )
                                                            }));
                                                            
                                                            console.log('Before change:', changes);
                                                            const newChanges = {
                                                                ...changes,
                                                                [donationId]: [...new Set([
                                                                    ...(changes[donationId] || []),
                                                                    subsidy.chidon_user_subsidy_id
                                                                ])]
                                                            };
                                                            console.log('After change:', newChanges);
                                                            setChanges(newChanges);
                                                        }}
                                                    />
                                                </td>
                                            </tr>
                                        ))}
                                        <tr>
                                            <td>
                                                <button 
                                                    className="btn btn-primary"
                                                    disabled={!changes[donationId] || changes[donationId].length === 0}
                                                    onClick={() => {
                                                        console.log('Updating changes for donation:', donationId);
                                                        console.log('Changes:', changes);
                                                        updateSubsidies(donationId);
                                                    }}
                                                >
                                                    Update Changes {changes[donationId] ? `(${changes[donationId].length})` : ''}
                                                </button>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

ReactDOM.render(<DonationManager />, document.getElementById('root'));